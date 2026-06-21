<?php

namespace Modules\Academic\Http\Controllers\Api;

use Modules\Academic\Entities\ExamSchedule;
use Modules\Academic\Entities\ExamResult;
use Modules\Academic\Entities\Student;
use Modules\Academic\Entities\Grade;
use Modules\Core\Services\StudentSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MarkController extends \Modules\Core\Http\Controllers\Api\Controller
{
    public function __construct(
        private readonly StudentSessionService $studentSessionService
    ) {
        $this->setControllerName('MarkController');
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $studentSession = $this->studentSessionService->getStudentSession($user);

        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }

        $reportcard = ExamSchedule::getExamsByClassAndSection(
            $studentSession->class_id,
            $studentSession->section_id,
            $studentSession->session_id
        );

        $examSchedule = [];
        if ($reportcard->isNotEmpty()) {
            $examIds = $reportcard->pluck('exam_id')->toArray();
            $allResults = DB::table('exam_schedules')
                ->join('teacher_subjects', 'teacher_subjects.id', '=', 'exam_schedules.teacher_subject_id')
                ->join('exam_results', 'exam_results.exam_schedule_id', '=', 'exam_schedules.id')
                ->join('subjects', 'subjects.id', '=', 'teacher_subjects.subject_id')
                ->whereIn('exam_schedules.exam_id', $examIds)
                ->where('teacher_subjects.session_id', $studentSession->session_id)
                ->where('exam_results.student_id', $studentSession->student_id)
                ->select(
                    'exam_schedules.id as exam_schedule_id',
                    'exam_schedules.full_marks',
                    'exam_schedules.exam_id',
                    'exam_schedules.passing_marks',
                    'exam_results.attendence',
                    'exam_results.get_marks',
                    'exam_results.note',
                    'subjects.name',
                    'subjects.code',
                    'subjects.type'
                )
                ->get()
                ->groupBy('exam_id');

            foreach ($reportcard as $exam) {
                $examResults = $allResults->get($exam->exam_id, collect());
                foreach ($examResults as $result) {
                    $examSchedule[] = [
                        'exam_id' => $result->exam_id,
                        'exam_schedule_id' => $result->exam_schedule_id,
                        'full_marks' => $result->full_marks,
                        'passing_marks' => $result->passing_marks,
                        'exam_name' => $exam->name ?? $result->name ?? 'Exam',
                        'get_marks' => $result->get_marks,
                        'attendence' => $result->attendence,
                    ];
                }
            }
        }

        $data = [
            'class_id' => $studentSession->class_id,
            'section_id' => $studentSession->section_id,
            'examSchedule' => $examSchedule,
        ];

        return $this->successResponse($data);
    }

    public function marklist(Request $request): JsonResponse
    {
        $user = $request->user();
        $studentSession = $this->studentSessionService->getStudentSession($user);

        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }

        $student = Student::find($studentSession->student_id);

        $gradeList = Grade::where('is_active', 'yes')->get();

        $examList = ExamSchedule::getExamsByClassAndSection(
            $studentSession->class_id,
            $studentSession->section_id,
            $studentSession->session_id
        );

        $examSchedule = [];
        if ($examList->isNotEmpty()) {
            $examIds = $examList->pluck('exam_id')->toArray();
            $allResults = DB::table('exam_schedules')
                ->join('teacher_subjects', 'teacher_subjects.id', '=', 'exam_schedules.teacher_subject_id')
                ->join('exam_results', 'exam_results.exam_schedule_id', '=', 'exam_schedules.id')
                ->join('subjects', 'subjects.id', '=', 'teacher_subjects.subject_id')
                ->whereIn('exam_schedules.exam_id', $examIds)
                ->where('teacher_subjects.session_id', $studentSession->session_id)
                ->where('exam_results.student_id', $studentSession->student_id)
                ->select(
                    'exam_schedules.id as exam_schedule_id',
                    'exam_schedules.full_marks',
                    'exam_schedules.exam_id',
                    'exam_schedules.passing_marks',
                    'exam_results.attendence',
                    'exam_results.get_marks',
                    'exam_results.note',
                    'subjects.name',
                    'subjects.code',
                    'subjects.type'
                )
                ->get()
                ->groupBy('exam_id');

            foreach ($examList as $exam) {
                $examSub = $allResults->get($exam->exam_id, collect());
                $examSchedule[] = [
                    'exam_name' => $exam->name ?? 'Exam',
                    'exam_result' => $examSub,
                ];
            }
        }

        $data = [
            'title' => 'Student Details',
            'gradeList' => $gradeList,
            'examSchedule' => $examSchedule,
            'student' => $student,
        ];

        return $this->successResponse($data);
    }

    public function view($id, Request $request): JsonResponse
    {
        $user = $request->user();
        $studentSession = $this->studentSessionService->getStudentSession($user);

        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }

        $mark = ExamResult::where('id', $id)
            ->where('student_id', $studentSession->student_id)
            ->first();

        if (!$mark) {
            return $this->errorResponse('Mark not found', null, 404);
        }

        return $this->successResponse(['mark' => $mark]);
    }
}
