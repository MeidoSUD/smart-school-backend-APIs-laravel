<?php

namespace Modules\Staff\Http\Controllers\Api\Teacher;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Staff\Services\TeacherScope;

/**
 * إدارة الامتحانات الإلكترونية للمعلم — parity مع CI admin/Onlineexam:
 * - add (إنشاء) / delete + getRecord
 * - questionAdd/deleteExamQuestions/getExamQuestions (ربط أسئلة)
 * - assign/addstudent (إسناد طلاب نطاقه)
 * - evalution/fillmarks (تصحيح) + report/getstudentresult
 */
class TeacherOnlineExamManageController extends TeacherController
{
    public function __construct()
    {
        $this->setControllerName('TeacherOnlineExamManageController');
    }

    public function store(Request $request): JsonResponse
    {
        $ctx = $this->context($request);
        $v = $request->validate([
            'title' => 'required|string|max:255',
            'class_id' => 'required|integer',
            'section_id' => 'required|integer',
            'subject_id' => 'required|integer|exists:subjects,id',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'duration' => 'required|integer|min:1',
            'maximum_attempts' => 'nullable|integer|min:1',
            'passing_percentage' => 'nullable|numeric|min:0|max:100',
            'description' => 'nullable|string',
        ]);

        $csId = (int) DB::table('class_sections')
            ->where('class_id', $v['class_id'])->where('section_id', $v['section_id'])->value('id');
        if (! $csId || ! TeacherScope::inScope((int) $ctx->staff->id, $ctx->sessionId, $csId)) {
            return $this->errorResponse('Class section is not in your scope', null, 403);
        }

        // CI parity (admin/Onlineexam::add → onlineexam_model::add on table `onlineexam`):
        // title→`exam`, attempt→`attempt`, passing_percentage stays. Laravel table
        // `online_exams` only has exam_title/max_attempts/minimum_percentage/duration
        // (see OnlineExamSeeder mapping); date_from/date_to/description/created_by
        // have no columns here and are accepted-but-not-persisted (no schema change).
        $id = DB::table('online_exams')->insertGetId([
            'exam_title' => $v['title'],
            'class_id' => $v['class_id'],
            'section_id' => $v['section_id'],
            'subject_id' => $v['subject_id'],
            'duration' => $v['duration'],
            'max_attempts' => $v['maximum_attempts'] ?? 1,
            'minimum_percentage' => $v['passing_percentage'] ?? 0,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $this->successResponse(['id' => $id], 'Online exam created successfully');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $ctx = $this->context($request);
        $exam = DB::table('online_exams')->where('id', $id)->first();
        if (! $exam) {
            return $this->errorResponse('Exam not found', null, 404);
        }
        $csId = (int) DB::table('class_sections')
            ->where('class_id', $exam->class_id)->where('section_id', $exam->section_id)->value('id');
        if (! $csId || ! TeacherScope::inScope((int) $ctx->staff->id, $ctx->sessionId, $csId)) {
            return $this->errorResponse('Exam is not in your scope', null, 403);
        }

        DB::table('online_exams')->where('id', $id)->delete();

        return $this->successResponse(null, 'Online exam deleted successfully');
    }

    public function examQuestions(Request $request, int $examId): JsonResponse
    {
        $this->context($request);
        // CI parity (Onlineexamquestion_model::getExamQuestions):
        // per-question marks = IFNULL(onlineexam_questions.marks,1). Laravel table
        // `online_exam_questions` has no marks column (only optional), and neither
        // does `questions`, so map to the CI default constant (no schema change).
        $rows = DB::table('online_exam_questions')
            ->join('questions', 'questions.id', '=', 'online_exam_questions.question_id')
            ->where('online_exam_questions.online_exam_id', $examId)
            ->select('questions.*', DB::raw('1 as exam_marks'))
            ->get();

        return $this->successResponse(['questions' => $rows]);
    }

    public function attachQuestions(Request $request): JsonResponse
    {
        $this->context($request);
        $v = $request->validate([
            'online_exam_id' => 'required|integer|exists:online_exams,id',
            'question_ids' => 'required|array|min:1',
            'question_ids.*' => 'integer|exists:questions,id',
            'marks' => 'nullable|numeric|min:0',
        ]);

        // CI parity (admin/Onlineexam::questionAdd): link row only. Laravel table
        // `online_exam_questions` has no marks/neg_marks columns, so the accepted
        // `marks` input is not persisted (no schema change).
        foreach ($v['question_ids'] as $qid) {
            DB::table('online_exam_questions')->updateOrInsert(
                ['online_exam_id' => $v['online_exam_id'], 'question_id' => $qid],
                ['updated_at' => now(), 'created_at' => now()]
            );
        }

        return $this->successResponse(null, 'Questions attached successfully');
    }

    public function detachQuestion(Request $request): JsonResponse
    {
        $this->context($request);
        $v = $request->validate([
            'online_exam_id' => 'required|integer',
            'question_id' => 'required|integer',
        ]);
        DB::table('online_exam_questions')
            ->where('online_exam_id', $v['online_exam_id'])->where('question_id', $v['question_id'])->delete();

        return $this->successResponse(null, 'Question detached successfully');
    }

    public function assignStudents(Request $request): JsonResponse
    {
        $ctx = $this->context($request);
        $v = $request->validate([
            'online_exam_id' => 'required|integer|exists:online_exams,id',
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'integer|exists:students,id',
        ]);

        $exam = DB::table('online_exams')->where('id', $v['online_exam_id'])->first();
        $csId = (int) DB::table('class_sections')
            ->where('class_id', $exam->class_id)->where('section_id', $exam->section_id)->value('id');
        if (! $csId || ! TeacherScope::inScope((int) $ctx->staff->id, $ctx->sessionId, $csId)) {
            return $this->errorResponse('Exam is not in your scope', null, 403);
        }

        // CI parity (admin/Onlineexam::addstudent → onlineexam_model::addStudents):
        // table `onlineexam_students` keys on (onlineexam_id, student_session_id),
        // NOT (online_exam_id, student_id). Resolve students.id → student_session.id
        // in the current session before linking.
        $sessionIds = DB::table('student_session')
            ->whereIn('student_id', $v['student_ids'])
            ->where('session_id', $ctx->sessionId)
            ->pluck('id');

        foreach ($sessionIds as $ssId) {
            DB::table('onlineexam_students')->updateOrInsert(
                ['onlineexam_id' => $v['online_exam_id'], 'student_session_id' => $ssId],
                ['updated_at' => now(), 'created_at' => now()]
            );
        }

        return $this->successResponse(null, 'Students assigned successfully');
    }

    public function report(Request $request, int $examId): JsonResponse
    {
        $this->context($request);
        $rows = DB::table('online_exam_results')
            ->join('students', 'students.id', '=', 'online_exam_results.student_id')
            ->where('online_exam_results.online_exam_id', $examId)
            ->select('students.id', 'students.admission_no', 'students.firstname', 'students.lastname',
                'online_exam_results.obtained_marks', 'online_exam_results.attended_on')
            ->orderByDesc('online_exam_results.obtained_marks')->get();

        return $this->successResponse(['results' => $rows]);
    }

    public function saveDescriptiveMarks(Request $request): JsonResponse
    {
        $this->context($request);
        $v = $request->validate([
            'online_exam_id' => 'required|integer',
            'student_id' => 'required|integer',
            'obtained_marks' => 'required|numeric|min:0',
        ]);

        DB::table('online_exam_results')->updateOrInsert(
            ['online_exam_id' => $v['online_exam_id'], 'student_id' => $v['student_id']],
            ['obtained_marks' => $v['obtained_marks'], 'attended_on' => now(), 'updated_at' => now()]
        );

        return $this->successResponse(null, 'Marks saved successfully');
    }
}
