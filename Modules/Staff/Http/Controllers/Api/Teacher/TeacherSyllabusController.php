<?php

namespace Modules\Staff\Http\Controllers\Api\Teacher;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Staff\Services\TeacherScope;

/**
 * المنهج وخطة الدرس — parity مع:
 * - CI admin/Syllabus::add_syllabus (topic/date/time + created_by) و status
 * - CI admin/Lessonplan::lesson/topic (نطاق المعلم role_id==2)
 * ربط النطاق: دروس مجموعة-المواد المرتبطة بمواد المعلم وفصوله.
 */
class TeacherSyllabusController extends TeacherController
{
    public function __construct()
    {
        $this->setControllerName('TeacherSyllabusController');
    }

    /** subject_group_subject_ids المسندة للمعلم */
    private function mySgsIds(int $staffId, int $sessionId): array
    {
        $classes = TeacherScope::myClasses($staffId, $sessionId);
        $csIds = collect($classes)->pluck('class_section_id')->all();
        if (empty($csIds)) return [];

        $subjectIds = DB::table('teacher_subjects')->where('teacher_id', $staffId)
            ->whereIn('class_section_id', $csIds)
            ->where('session_id', $sessionId)
            ->distinct()->pluck('subject_id')->all();

        $groupIds = DB::table('subject_group_class_sections')->whereIn('class_section_id', $csIds)
            ->where('session_id', $sessionId)
            ->distinct()->pluck('subject_group_id')->all();

        if (empty($subjectIds) || empty($groupIds)) return [];

        return DB::table('subject_group_subjects')
            ->whereIn('subject_id', $subjectIds)->whereIn('subject_group_id', $groupIds)
            ->pluck('id')->map(fn ($x) => (int) $x)->all();
    }

    public function lessons(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        $ids = $this->mySgsIds((int) $ctx->staff->id, $ctx->sessionId);
        $lessons = DB::table('lesson')
            ->leftJoin('subject_group_subjects as sgs', 'sgs.id', '=', 'lesson.subject_group_subject_id')
            ->leftJoin('subjects', 'subjects.id', '=', 'sgs.subject_id')
            ->when(! empty($ids), fn ($q) => $q->whereIn('lesson.subject_group_subject_id', $ids))
            ->when(empty($ids), fn ($q) => $q->whereRaw('1=0'))
            ->select('lesson.*', 'subjects.name as subject_name')
            ->orderBy('lesson.id')->get();

        return $this->successResponse(['lessons' => $lessons]);
    }

    public function storeLesson(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        $v = $request->validate([
            'subject_group_subject_id' => 'required|integer',
            'name' => 'required|string|max:255',
        ]);

        if (! in_array((int) $v['subject_group_subject_id'], $this->mySgsIds((int) $ctx->staff->id, $ctx->sessionId), true)) {
            return $this->errorResponse('Subject is not in your scope', null, 403);
        }

        // CI parity (Lessonplan::createlesson): subject_group_class_sections_id
        // is resolved via getsubject_group_class_sectionsId(), constrained here
        // to the teacher's in-scope class sections.
        $sgs = DB::table('subject_group_subjects')->where('id', (int) $v['subject_group_subject_id'])->first();
        $csIds = collect(TeacherScope::myClasses((int) $ctx->staff->id, $ctx->sessionId))->pluck('class_section_id')->all();
        $sgcsId = ($sgs && $csIds) ? (int) DB::table('subject_group_class_sections')
            ->where('subject_group_id', $sgs->subject_group_id)
            ->whereIn('class_section_id', $csIds)
            ->where('session_id', $ctx->sessionId)
            ->orderByDesc('id')->value('id') : 0;
        if (! $sgcsId) {
            return $this->errorResponse('Cannot resolve class section for this subject', null, 422);
        }

        $id = DB::table('lesson')->insertGetId([
            'session_id' => $ctx->sessionId, 'subject_group_subject_id' => $v['subject_group_subject_id'],
            'subject_group_class_sections_id' => $sgcsId,
            'name' => $v['name'], 'created_at' => now(),
        ]);

        return $this->successResponse(['id' => $id], 'Lesson created successfully');
    }

    public function topics(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        $v = $request->validate(['lesson_id' => 'required|integer']);
        $lesson = DB::table('lesson')->where('id', $v['lesson_id'])->first();
        if (! $lesson || ! in_array((int) $lesson->subject_group_subject_id, $this->mySgsIds((int) $ctx->staff->id, $ctx->sessionId), true)) {
            return $this->errorResponse('Lesson is not in your scope', null, $lesson ? 403 : 404);
        }

        $topics = DB::table('topic')->where('lesson_id', $v['lesson_id'])->orderBy('id')->get();

        return $this->successResponse(['topics' => $topics]);
    }

    public function storeTopic(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        $v = $request->validate([
            'lesson_id' => 'required|integer', 'name' => 'required|string|max:255',
        ]);
        $lesson = DB::table('lesson')->where('id', $v['lesson_id'])->first();
        if (! $lesson || ! in_array((int) $lesson->subject_group_subject_id, $this->mySgsIds((int) $ctx->staff->id, $ctx->sessionId), true)) {
            return $this->errorResponse('Lesson is not in your scope', null, $lesson ? 403 : 404);
        }

        $id = DB::table('topic')->insertGetId([
            'session_id' => $ctx->sessionId, 'lesson_id' => $v['lesson_id'],
            'name' => $v['name'], 'status' => 0, 'created_at' => now(),
        ]);

        return $this->successResponse(['id' => $id], 'Topic created successfully');
    }

    public function syllabus(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        $v = $request->validate(['topic_id' => 'nullable|integer', 'lesson_id' => 'nullable|integer']);
        $query = DB::table('subject_syllabus')
            ->leftJoin('topic', 'topic.id', '=', 'subject_syllabus.topic_id')
            ->leftJoin('lesson', 'lesson.id', '=', 'topic.lesson_id')
            ->when($ctx->sessionId, fn ($q) => $q->where('subject_syllabus.session_id', $ctx->sessionId))
            ->select('subject_syllabus.*', 'topic.name as topic_name', 'lesson.name as lesson_name')
            ->orderByDesc('subject_syllabus.date');

        if (! empty($v['topic_id'])) $query->where('subject_syllabus.topic_id', $v['topic_id']);
        if (! empty($v['lesson_id'])) {
            $topicIds = DB::table('topic')->where('lesson_id', $v['lesson_id'])->pluck('id')->all();
            $query->whereIn('subject_syllabus.topic_id', $topicIds ?: [0]);
        }

        // نطاق المعلم: مواضيع دروسه فقط
        $myTopicIds = DB::table('topic')->whereIn('lesson_id',
            DB::table('lesson')->whereIn('subject_group_subject_id', $this->mySgsIds((int) $ctx->staff->id, $ctx->sessionId))->pluck('id')
        )->pluck('id')->all();
        $query->whereIn('subject_syllabus.topic_id', $myTopicIds ?: [0]);

        return $this->successResponse(['syllabus' => $query->paginate(25)]);
    }

    public function storeSyllabus(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        $v = $request->validate([
            'topic_id' => 'required|integer|exists:topic,id',
            'date' => 'required|date', 'time_from' => 'required|string|max:20',
            'time_to' => 'required|string|max:20',
            'presentation' => 'nullable|string', 'sub_topic' => 'nullable|string',
            'teaching_method' => 'nullable|string', 'general_objectives' => 'nullable|string',
            'previous_knowledge' => 'nullable|string',
            'comprehensive_questions' => 'nullable|string',
            'lacture_youtube_url' => 'nullable|string|max:500',
        ]);

        $topic = DB::table('topic')->where('id', $v['topic_id'])->first();
        $lesson = $topic ? DB::table('lesson')->where('id', $topic->lesson_id)->first() : null;
        if (! $lesson || ! in_array((int) $lesson->subject_group_subject_id, $this->mySgsIds((int) $ctx->staff->id, $ctx->sessionId), true)) {
            return $this->errorResponse('Topic is not in your scope', null, 403);
        }

        $id = DB::table('subject_syllabus')->insertGetId([
            'session_id' => $ctx->sessionId, 'topic_id' => $v['topic_id'], 'date' => $v['date'],
            'time_from' => $v['time_from'], 'time_to' => $v['time_to'],
            'presentation' => $v['presentation'] ?? null, 'sub_topic' => $v['sub_topic'] ?? null,
            'teaching_method' => $v['teaching_method'] ?? null,
            'general_objectives' => $v['general_objectives'] ?? null,
            'previous_knowledge' => $v['previous_knowledge'] ?? null,
            'comprehensive_questions' => $v['comprehensive_questions'] ?? null,
            'lacture_youtube_url' => $v['lacture_youtube_url'] ?? null,
            'created_by' => $ctx->staff->id, 'status' => 0, 'created_at' => now(),
        ]);

        return $this->successResponse(['id' => $id], 'Syllabus entry created successfully');
    }

    /** نسبة الإنجاز (complete/incomplete) مثل CI User::dashboard */
    public function status(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        $v = $request->validate([
            'class_id' => 'nullable|integer',
            'section_id' => 'nullable|integer',
            'subject_group_id' => 'nullable|integer',
        ]);

        // بدون فلاتر: الإجمالي العام لنطاق المعلم (للتوافق الخلفي)
        if (empty($v['class_id']) && empty($v['section_id']) && empty($v['subject_group_id'])) {
            $ids = $this->mySgsIds((int) $ctx->staff->id, $ctx->sessionId);
            $lessonIds = DB::table('lesson')->whereIn('subject_group_subject_id', $ids ?: [0])->pluck('id')->all();

            $total = DB::table('topic')->whereIn('lesson_id', $lessonIds ?: [0])->count();
            $complete = DB::table('topic')->whereIn('lesson_id', $lessonIds ?: [0])->where('status', 1)->count();

            return $this->successResponse([
                'total' => $total, 'complete' => $complete, 'incomplete' => $total - $complete,
                'complete_percent' => $total ? round($complete * 100 / $total, 2) : 0,
            ]);
        }

        // مع فلاتر: تقرير مفصّل per-subject — parity مع CI Report::lesson_plan
        return $this->report($request);
    }

    /**
     * تقرير الخطة الدراسية — parity مع CI Report::lesson_plan.
     * CI يعرض لكل مادة (label/complete/incomplete/total + lesson_summary بالمواضيع)
     * مع مخطط Chart.js؛ هنا نفس البنية + مجموعة بيانات جاهزة للرسم (chart).
     * الفلاتر الثلاثة إلزامية كما في CI (class_id/section_id/subject_group_id).
     */
    public function report(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        $v = $request->validate([
            'class_id' => 'required|integer',
            'section_id' => 'required|integer',
            'subject_group_id' => 'required|integer',
        ]);

        $csId = (int) DB::table('class_sections')
            ->where('class_id', $v['class_id'])->where('section_id', $v['section_id'])->value('id');
        if (! $csId || ! TeacherScope::inScope((int) $ctx->staff->id, $ctx->sessionId, $csId)) {
            return $this->errorResponse('Class section is not in your scope', null, 403);
        }

        $sgcs = DB::table('subject_group_class_sections')
            ->where('class_section_id', $csId)
            ->where('subject_group_id', $v['subject_group_id'])
            ->when($ctx->sessionId, fn ($q) => $q->where('session_id', $ctx->sessionId))
            ->first();
        if (! $sgcs) {
            return $this->errorResponse('Subject group is not linked to this class section', null, 404);
        }

        $mySubjectIds = DB::table('teacher_subjects')
            ->where('teacher_id', $ctx->staff->id)
            ->where('class_section_id', $csId)
            ->where('session_id', $ctx->sessionId)
            ->distinct()->pluck('subject_id')->map(fn ($id): int => (int) $id)->all();

        $groupSubjects = DB::table('subject_group_subjects as sgs')
            ->join('subjects', 'subjects.id', '=', 'sgs.subject_id')
            ->where('sgs.subject_group_id', $v['subject_group_id'])
            ->select('sgs.id as sgs_id', 'subjects.id as subject_id', 'subjects.name', 'subjects.code')
            ->orderBy('subjects.name')->get();

        $subjectsData = [];
        foreach ($groupSubjects as $gs) {
            $inScope = in_array((int) $gs->subject_id, $mySubjectIds, true);
            $label = ($gs->code === null || $gs->code === '') ? $gs->name : $gs->name.' ('.$gs->code.')';

            $lessons = DB::table('lesson')
                ->where('subject_group_subject_id', $gs->sgs_id)
                ->orderBy('id')->get(['id', 'name']);

            $topicRows = DB::table('topic')
                ->whereIn('lesson_id', $lessons->pluck('id')->all() ?: [0])
                ->orderBy('id')->get(['id', 'lesson_id', 'name', 'status', 'complete_date']);

            $total = $topicRows->count();
            $done = $topicRows->where('status', 1)->count();
            $completePct = $total > 0 ? (int) round($done * 100 / $total) : 0;

            $lessonSummary = $lessons->map(function ($lesson) use ($topicRows) {
                $topics = $topicRows->where('lesson_id', $lesson->id)->values();
                $tTotal = $topics->count();
                $tDone = $topics->where('status', 1)->count();

                return [
                    'id' => $lesson->id,
                    'name' => $lesson->name,
                    'topics' => $topics->map(fn ($t) => [
                        'id' => $t->id, 'name' => $t->name,
                        'status' => (int) $t->status, 'complete_date' => $t->complete_date,
                    ])->values(),
                    'complete_percent' => $tTotal > 0 ? (int) round($tDone * 100 / $tTotal) : 0,
                    'incomplete_percent' => $tTotal > 0 ? (int) round(($tTotal - $tDone) * 100 / $tTotal) : 0,
                ];
            })->values();

            $subjectsData[] = [
                'subject_id' => $gs->subject_id,
                'lebel' => $label,
                'name' => $gs->name,
                'code' => $gs->code,
                'in_scope' => $inScope,
                'complete' => $completePct,
                'incomplete' => $total > 0 ? 100 - $completePct : 0,
                'total' => $total,
                'complete_count' => $done,
                'incomplete_count' => $total - $done,
                'lesson_summary' => $inScope ? $lessonSummary : [],
            ];
        }

        $scoped = array_values(array_filter($subjectsData, fn ($s) => $s['in_scope']));
        $grandTotal = array_sum(array_column($scoped, 'total'));
        $grandDone = array_sum(array_column($scoped, 'complete_count'));

        return $this->successResponse([
            'class_id' => (int) $v['class_id'],
            'section_id' => (int) $v['section_id'],
            'subject_group_id' => (int) $v['subject_group_id'],
            'subjects_data' => $subjectsData,
            'summary' => [
                'total' => $grandTotal,
                'complete' => $grandDone,
                'incomplete' => $grandTotal - $grandDone,
                'complete_percent' => $grandTotal > 0 ? round($grandDone * 100 / $grandTotal, 2) : 0,
            ],
            // مجموعة جاهزة لمخطط Chart.js كما في view reports/syllabus في CI
            'chart' => [
                'labels' => array_column($scoped, 'lebel'),
                'complete' => array_column($scoped, 'complete'),
                'incomplete' => array_column($scoped, 'incomplete'),
            ],
        ]);
    }
}
