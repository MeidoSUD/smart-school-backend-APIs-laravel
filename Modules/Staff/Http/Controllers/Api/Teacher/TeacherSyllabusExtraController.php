<?php

namespace Modules\Staff\Http\Controllers\Api\Teacher;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Staff\Services\TeacherScope;

/**
 * إضافات المنهج/الخطة — parity مع CI Lessonplan/Syllabus:
 * - copyLesson/saveCopyLesson (نسخ دروس سنة/فصل)
 * - deleteLesson/deleteLessonBulk + deleteTopicBulk + changeTopicStatus/topic_completedate
 * - syllabus messages: addmessage/getmessage/deletemessage (lesson_plan_forum)
 * - deleteSubjectSyllabus
 *
 * النطاق: كل عملية تتحقق أن الدرس/الموضوع/مفردة المنهج تابعة لمواد المعلم
 * (subject_group_subjects ضمن فصوله) — وإلا 403/404. بدون هذا الفحص يمكن لأي
 * معلم تعديل منهج معلم آخر (IDOR).
 */
class TeacherSyllabusExtraController extends TeacherController
{
    public function __construct()
    {
        $this->setControllerName('TeacherSyllabusExtraController');
    }

    public function copyLesson(Request $request): JsonResponse
    {
        $ctx = $this->context($request);
        $v = $request->validate([
            'from_subject_group_subject_id' => 'required|integer',
            'to_subject_group_subject_id' => 'required|integer',
        ]);

        $allowed = $this->mySgsIds((int) $ctx->staff->id, $ctx->sessionId);
        if (! in_array((int) $v['from_subject_group_subject_id'], $allowed, true)
            || ! in_array((int) $v['to_subject_group_subject_id'], $allowed, true)) {
            return $this->errorResponse('Subject is not in your scope', null, 403);
        }

        $lessons = DB::table('lesson')->where('subject_group_subject_id', $v['from_subject_group_subject_id'])->get();
        $copied = 0;
        DB::transaction(function () use ($lessons, $v, $ctx, &$copied) {
            foreach ($lessons as $l) {
                $newLessonId = DB::table('lesson')->insertGetId([
                    'session_id' => $ctx->sessionId,
                    'subject_group_subject_id' => $v['to_subject_group_subject_id'],
                    'name' => $l->name,
                    'created_at' => now(),
                ]);
                $topics = DB::table('topic')->where('lesson_id', $l->id)->get();
                foreach ($topics as $t) {
                    DB::table('topic')->insert([
                        'session_id' => $ctx->sessionId,
                        'lesson_id' => $newLessonId,
                        'name' => $t->name,
                        'status' => 0,
                        'created_at' => now(),
                    ]);
                }
                $copied++;
            }
        });

        return $this->successResponse(['copied_lessons' => $copied], 'Lessons copied successfully');
    }

    public function deleteLesson(Request $request, int $id): JsonResponse
    {
        $ctx = $this->context($request);
        $lesson = DB::table('lesson')->where('id', $id)->first();
        if (! $lesson) {
            return $this->errorResponse('Lesson not found', null, 404);
        }
        if (! in_array((int) $lesson->subject_group_subject_id, $this->mySgsIds((int) $ctx->staff->id, $ctx->sessionId), true)) {
            return $this->errorResponse('Lesson is not in your scope', null, 403);
        }
        DB::table('topic')->where('lesson_id', $id)->delete();
        DB::table('lesson')->where('id', $id)->delete();

        return $this->successResponse(null, 'Lesson deleted successfully');
    }

    public function deleteTopicBulk(Request $request): JsonResponse
    {
        $ctx = $this->context($request);
        $v = $request->validate(['ids' => 'required|array|min:1', 'ids.*' => 'integer']);
        $allowed = $this->mySgsIds((int) $ctx->staff->id, $ctx->sessionId);
        $outOfScope = DB::table('topic')
            ->join('lesson', 'lesson.id', '=', 'topic.lesson_id')
            ->whereIn('topic.id', $v['ids'])
            ->whereNotIn('lesson.subject_group_subject_id', $allowed ?: [0])
            ->exists();
        if ($outOfScope) {
            return $this->errorResponse('One or more topics are not in your scope', null, 403);
        }
        DB::table('topic')->whereIn('id', $v['ids'])->delete();

        return $this->successResponse(null, 'Topics deleted successfully');
    }

    public function changeTopicStatus(Request $request): JsonResponse
    {
        $ctx = $this->context($request);
        $v = $request->validate([
            'topic_id' => 'required|integer|exists:topic,id',
            'status' => 'required|integer|in:0,1',
            'complete_date' => 'nullable|date',
        ]);

        if (! $this->topicInScope((int) $v['topic_id'], (int) $ctx->staff->id, $ctx->sessionId)) {
            return $this->errorResponse('Topic is not in your scope', null, 403);
        }
        DB::table('topic')->where('id', $v['topic_id'])->update([
            'status' => $v['status'],
            'complete_date' => $v['complete_date'] ?? ($v['status'] ? date('Y-m-d') : null),
        ]);

        return $this->successResponse(null, 'Topic status updated successfully');
    }

    public function syllabusMessages(Request $request, int $syllabusId): JsonResponse
    {
        $ctx = $this->context($request);
        if (! $this->syllabusInScope($syllabusId, (int) $ctx->staff->id, $ctx->sessionId)) {
            return $this->errorResponse('Syllabus entry is not in your scope', null, 403);
        }
        $rows = DB::table('lesson_plan_forum')->where('subject_syllabus_id', $syllabusId)
            ->orderBy('id')->get();

        return $this->successResponse(['messages' => $rows]);
    }

    public function addSyllabusMessage(Request $request): JsonResponse
    {
        $ctx = $this->context($request);
        $v = $request->validate([
            'subject_syllabus_id' => 'required|integer',
            'message' => 'required|string|max:2000',
        ]);

        if (! $this->syllabusInScope((int) $v['subject_syllabus_id'], (int) $ctx->staff->id, $ctx->sessionId)) {
            return $this->errorResponse('Syllabus entry is not in your scope', null, 403);
        }
        $id = DB::table('lesson_plan_forum')->insertGetId([
            'subject_syllabus_id' => $v['subject_syllabus_id'],
            'message' => $v['message'],
            'created_by' => $ctx->staff->id,
            'created_at' => now(),
        ]);

        return $this->successResponse(['id' => $id], 'Message added successfully');
    }

    public function deleteSyllabusMessage(Request $request, int $id): JsonResponse
    {
        $ctx = $this->context($request);
        $msg = DB::table('lesson_plan_forum')->where('id', $id)->first();
        if (! $msg) {
            return $this->errorResponse('Message not found', null, 404);
        }
        if (! $this->syllabusInScope((int) $msg->subject_syllabus_id, (int) $ctx->staff->id, $ctx->sessionId)) {
            return $this->errorResponse('Message is not in your scope', null, 403);
        }
        DB::table('lesson_plan_forum')->where('id', $id)->delete();

        return $this->successResponse(null, 'Message deleted successfully');
    }

    public function deleteSyllabus(Request $request, int $id): JsonResponse
    {
        $ctx = $this->context($request);
        $row = DB::table('subject_syllabus')->where('id', $id)->first();
        if (! $row) {
            return $this->errorResponse('Syllabus entry not found', null, 404);
        }
        if (! $this->syllabusInScope($id, (int) $ctx->staff->id, $ctx->sessionId)) {
            return $this->errorResponse('Syllabus entry is not in your scope', null, 403);
        }
        DB::table('lesson_plan_forum')->where('subject_syllabus_id', $id)->delete();
        DB::table('subject_syllabus')->where('id', $id)->delete();

        return $this->successResponse(null, 'Syllabus entry deleted successfully');
    }

    /** subject_group_subject_ids المسندة للمعلم (مطابق لمنطق TeacherSyllabusController). */
    private function mySgsIds(int $staffId, int $sessionId): array
    {
        $classes = TeacherScope::myClasses($staffId, $sessionId);
        $csIds = collect($classes)->pluck('class_section_id')->all();
        if (empty($csIds)) {
            return [];
        }

        $subjectIds = DB::table('teacher_subjects')->where('teacher_id', $staffId)
            ->whereIn('class_section_id', $csIds)
            ->where('session_id', $sessionId)
            ->distinct()->pluck('subject_id')->all();

        $groupIds = DB::table('subject_group_class_sections')->whereIn('class_section_id', $csIds)
            ->where('session_id', $sessionId)
            ->distinct()->pluck('subject_group_id')->all();

        if (empty($subjectIds) || empty($groupIds)) {
            return [];
        }

        return DB::table('subject_group_subjects')
            ->whereIn('subject_id', $subjectIds)->whereIn('subject_group_id', $groupIds)
            ->pluck('id')->map(fn ($x) => (int) $x)->all();
    }

    private function topicInScope(int $topicId, int $staffId, int $sessionId): bool
    {
        $lessonId = DB::table('topic')->where('id', $topicId)->value('lesson_id');
        if (! $lessonId) {
            return false;
        }
        $sgsId = (int) DB::table('lesson')->where('id', $lessonId)->value('subject_group_subject_id');

        return in_array($sgsId, $this->mySgsIds($staffId, $sessionId), true);
    }

    private function syllabusInScope(int $syllabusId, int $staffId, int $sessionId): bool
    {
        $topicId = DB::table('subject_syllabus')->where('id', $syllabusId)->value('topic_id');
        if (! $topicId) {
            return false;
        }

        return $this->topicInScope((int) $topicId, $staffId, $sessionId);
    }
}
