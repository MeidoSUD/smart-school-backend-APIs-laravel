<?php

namespace Modules\Staff\Http\Controllers\Api\Teacher;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Staff\Services\TeacherScope;

/**
 * امتحانات المعلم — parity مع CI (نطاق role_id==2):
 * - admin/Onlineexam (امتحانات فصول المعلم: class/section ضمن نطاقه)
 * - admin/Question (بنك الأسئلة: أسئلته staff_id أو فصوله class_section_id)
 * - admit/marksheet للعرض ضمن النطاق
 */
class TeacherExamController extends TeacherController
{
    public function __construct()
    {
        $this->setControllerName('TeacherExamController');
    }

    private function buildOnlineExamsQuery(TeacherContext $ctx, ?string $tab = null, ?string $search = null)
    {
        $myClasses = TeacherScope::myClasses((int) $ctx->staff->id, $ctx->sessionId);
        $scopedStudentSessionIds = DB::table('student_session')
            ->where(function ($q) use ($myClasses) {
                foreach ($myClasses as $c) {
                    $q->orWhere(fn ($qq) => $qq->where('class_id', $c['class_id'])->where('section_id', $c['section_id']));
                }
            })
            ->when($ctx->sessionId, fn ($q) => $q->where('session_id', $ctx->sessionId))
            ->pluck('id')
            ->all();

        $assignedExamIds = DB::table('onlineexam_students')
            ->whereIn('student_session_id', $scopedStudentSessionIds)
            ->pluck('onlineexam_id')
            ->all();

        $unassignedExamIds = DB::table('onlineexam')
            ->leftJoin('onlineexam_students', 'onlineexam_students.onlineexam_id', '=', 'onlineexam.id')
            ->whereNull('onlineexam_students.onlineexam_id')
            ->pluck('onlineexam.id')
            ->all();

        $allowedExamIds = array_values(array_unique(array_merge($assignedExamIds, $unassignedExamIds)));

        $now = now()->format('Y-m-d H:i:s');

        $query = DB::table('onlineexam')
            ->when($ctx->sessionId, fn ($q) => $q->where('onlineexam.session_id', $ctx->sessionId))
            ->whereIn('onlineexam.id', ! empty($allowedExamIds) ? $allowedExamIds : [0])
            ->select([
                'onlineexam.*',
                'onlineexam.exam as exam_title',
                'onlineexam.attempt as max_attempts',
                'onlineexam.passing_percentage as minimum_percentage',
                DB::raw('(select count(*) from onlineexam_questions where onlineexam_questions.onlineexam_id = onlineexam.id) as total_ques'),
                DB::raw('(select count(*) from onlineexam_questions left join questions on questions.id = onlineexam_questions.question_id where onlineexam_questions.onlineexam_id = onlineexam.id and questions.question_type = "descriptive") as total_descriptive_ques'),
                DB::raw('case when onlineexam.exam_to >= "' . $now . '" then "upcoming" else "closed" end as exam_status'),
            ]);

        if ($tab === 'upcoming') {
            $query->where('onlineexam.exam_to', '>=', $now)->orderByDesc('onlineexam.exam_from');
        } elseif ($tab === 'closed') {
            $query->where('onlineexam.exam_to', '<', $now)->orderByDesc('onlineexam.exam_to');
        } else {
            $query->orderByDesc('onlineexam.id');
        }

        if (! empty($search)) {
            $s = '%' . $search . '%';
            $query->where(function ($q) use ($s) {
                $q->where('onlineexam.exam', 'like', $s)
                    ->orWhere('onlineexam.description', 'like', $s);
            });
        }

        return $query;
    }

    public function onlineExams(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        $tab = $request->query('tab') ?? $request->query('type');
        $search = $request->query('search');

        $query = $this->buildOnlineExamsQuery($ctx, $tab, $search);

        $now = now()->format('Y-m-d H:i:s');
        $upcomingCount = (clone $this->buildOnlineExamsQuery($ctx))->where('onlineexam.exam_to', '>=', $now)->count();
        $closedCount = (clone $this->buildOnlineExamsQuery($ctx))->where('onlineexam.exam_to', '<', $now)->count();

        $paginated = $query->paginate($request->integer('per_page', 25));

        return $this->successResponse([
            'online_exams' => $paginated,
            'upcoming_count' => $upcomingCount,
            'closed_count' => $closedCount,
        ]);
    }

    public function getexamlist(Request $request): JsonResponse
    {
        $ctx = $this->context($request);
        $search = $request->query('search');
        $query = $this->buildOnlineExamsQuery($ctx, 'upcoming', $search);

        return $this->successResponse([
            'online_exams' => $query->paginate($request->integer('per_page', 100)),
            'tab' => 'upcoming',
        ]);
    }

    public function getclosedexamlist(Request $request): JsonResponse
    {
        $ctx = $this->context($request);
        $search = $request->query('search');
        $query = $this->buildOnlineExamsQuery($ctx, 'closed', $search);

        return $this->successResponse([
            'online_exams' => $query->paginate($request->integer('per_page', 100)),
            'tab' => 'closed',
        ]);
    }

    public function questions(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        $v = $request->validate([
            'subject_id' => 'nullable|integer', 'search' => 'nullable|string|max:100',
        ]);

        $classes = TeacherScope::myClasses((int) $ctx->staff->id, $ctx->sessionId);
        $csIds = collect($classes)->pluck('class_section_id')->all();

        $query = DB::table('questions')
            ->leftJoin('subjects', 'subjects.id', '=', 'questions.subject_id')
            ->where(function ($q) use ($ctx, $csIds) {
                $q->where('questions.staff_id', $ctx->staff->id);
                if (! empty($csIds)) $q->orWhereIn('questions.class_section_id', $csIds);
            })
            ->select('questions.*', 'subjects.name as subject_name')
            ->orderByDesc('questions.id');

        if (! empty($v['subject_id'])) $query->where('questions.subject_id', $v['subject_id']);
        if (! empty($v['search'])) {
            $s = '%' . $v['search'] . '%';
            $query->where('questions.question', 'like', $s);
        }

        return $this->successResponse(['questions' => $query->paginate(25)]);
    }

    public function storeQuestion(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        $v = $request->validate([
            'subject_id' => 'required|integer|exists:subjects,id',
            'class_section_id' => 'required|integer',
            'question' => 'required|string', 'question_type' => 'nullable|string|max:50',
            'level' => 'nullable|string|max:50', 'opt_a' => 'nullable|string',
            'opt_b' => 'nullable|string', 'opt_c' => 'nullable|string',
            'opt_d' => 'nullable|string', 'opt_e' => 'nullable|string',
            'correct' => 'nullable|string|max:10',
        ]);

        if (! TeacherScope::inScope((int) $ctx->staff->id, $ctx->sessionId, (int) $v['class_section_id'])) {
            return $this->errorResponse('Class section is not in your scope', null, 403);
        }

        $cs = DB::table('class_sections')->where('id', $v['class_section_id'])->first();
        $id = DB::table('questions')->insertGetId([
            'staff_id' => $ctx->staff->id, 'subject_id' => $v['subject_id'],
            'class_id' => $cs->class_id ?? null, 'section_id' => $cs->section_id ?? null,
            'class_section_id' => $v['class_section_id'], 'question' => $v['question'],
            'question_type' => $v['question_type'] ?? 'singlechoice', 'level' => $v['level'] ?? 'easy',
            'opt_a' => $v['opt_a'] ?? null, 'opt_b' => $v['opt_b'] ?? null,
            'opt_c' => $v['opt_c'] ?? null, 'opt_d' => $v['opt_d'] ?? null,
            'opt_e' => $v['opt_e'] ?? null, 'correct' => $v['correct'] ?? null,
            'descriptive_word_limit' => 0,
        ]);

        return $this->successResponse(['id' => $id], 'Question created successfully');
    }

    /** parity مع CI Question::read — عرض سؤال من نطاقي */
    public function showQuestion(Request $request, int $id): JsonResponse
    {
        $ctx = $this->context($request);
        $q = DB::table('questions')->where('id', $id)->first();
        if (! $q) {
            return $this->errorResponse('Question not found', null, 404);
        }
        if ((int) $q->staff_id !== (int) $ctx->staff->id
            && ! TeacherScope::inScope((int) $ctx->staff->id, $ctx->sessionId, (int) $q->class_section_id)) {
            return $this->errorResponse('Question is not in your scope', null, 403);
        }

        return $this->successResponse(['question' => $q]);
    }

    /** parity مع CI Question::editform — تعديل سؤالي فقط */
    public function updateQuestion(Request $request, int $id): JsonResponse
    {
        $ctx = $this->context($request);
        $q = DB::table('questions')->where('id', $id)->where('staff_id', $ctx->staff->id)->first();
        if (! $q) {
            return $this->errorResponse('Question not found', null, 404);
        }

        $v = $request->validate([
            'question' => 'sometimes|string', 'question_type' => 'sometimes|nullable|string|max:50',
            'level' => 'sometimes|nullable|string|max:50', 'opt_a' => 'sometimes|nullable|string',
            'opt_b' => 'sometimes|nullable|string', 'opt_c' => 'sometimes|nullable|string',
            'opt_d' => 'sometimes|nullable|string', 'opt_e' => 'sometimes|nullable|string',
            'correct' => 'sometimes|nullable|string|max:10',
        ]);

        DB::table('questions')->where('id', $id)->update($v);

        return $this->successResponse(null, 'Question updated successfully');
    }

    /** parity مع CI Question::delete/bulkdelete — حذف أسئلتي فقط */
    public function deleteQuestion(Request $request, int $id): JsonResponse
    {
        $ctx = $this->context($request);
        $q = DB::table('questions')->where('id', $id)->where('staff_id', $ctx->staff->id)->first();
        if (! $q) {
            return $this->errorResponse('Question not found', null, 404);
        }

        DB::transaction(function () use ($id) {
            DB::table('online_exam_questions')->where('question_id', $id)->delete();
            DB::table('questions')->where('id', $id)->delete();
        });

        return $this->successResponse(null, 'Question deleted successfully');
    }

    public function deleteQuestionBulk(Request $request): JsonResponse
    {
        $ctx = $this->context($request);
        $v = $request->validate(['ids' => 'required|array|min:1', 'ids.*' => 'integer']);

        $mine = DB::table('questions')->where('staff_id', $ctx->staff->id)
            ->whereIn('id', $v['ids'])->pluck('id')->all();
        if ($mine !== []) {
            DB::transaction(function () use ($mine) {
                DB::table('online_exam_questions')->whereIn('question_id', $mine)->delete();
                DB::table('questions')->whereIn('id', $mine)->delete();
            });
        }

        return $this->successResponse(['deleted' => count($mine)], 'Questions deleted successfully');
    }
}
