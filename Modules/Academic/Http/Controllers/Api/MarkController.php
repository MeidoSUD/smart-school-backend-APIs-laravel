<?php

namespace Modules\Academic\Http\Controllers\Api;

use Modules\Academic\Entities\ExamSchedule;
use Modules\Academic\Entities\ExamResult;
use Modules\Academic\Entities\StudentSession;
use Modules\Academic\Entities\Student;
use Modules\Core\Entities\Setting;
use Modules\Academic\Entities\Grade;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MarkController extends \Modules\Core\Http\Controllers\Api\Controller
{
    public function __construct()
    {
        $this->setControllerName('MarkController');
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $studentSession = $this->getStudentSession($user);

        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }

        $reportcard = ExamSchedule::getExamsByClassAndSection(
            $studentSession->class_id,
            $studentSession->section_id,
            $studentSession->session_id
        );

        $examSchedule = [];
        foreach ($reportcard as $exam) {
            $examResults = ExamSchedule::getResultsByStudentAndExam(
                $exam->id,
                $studentSession->student_id,
                $studentSession->session_id
            );

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
        $studentSession = $this->getStudentSession($user);

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
        foreach ($examList as $exam) {
            $examSub = ExamSchedule::getResultsByStudentAndExam(
                $exam->id,
                $studentSession->student_id,
                $studentSession->session_id
            );

            $examSchedule[] = [
                'exam_name' => $exam->name ?? 'Exam',
                'exam_result' => $examSub,
            ];
        }

        $data = [
            'title' => 'Student Details',
            'gradeList' => $gradeList,
            'examSchedule' => $examSchedule,
            'student' => $student,
        ];

        return $this->successResponse($data);
    }

    public function view($id): JsonResponse
    {
        $mark = ExamResult::find($id);

        if (!$mark) {
            return $this->errorResponse('Mark not found', null, 404);
        }

        return $this->successResponse(['mark' => $mark]);
    }

    private function getStudentSession($user)
    {
        $studentId = null;

        if ($user->role === 'student') {
            $studentId = $user->user_id;
        } elseif ($user->role === 'parent') {
            $student = Student::where('parent_id', $user->id)->first();
            $studentId = $student ? $student->id : null;
        }

        if (!$studentId) {
            return null;
        }

        $setting = Setting::where('is_active', 1)->first();

        return StudentSession::where('student_id', $studentId)
            ->when($setting, fn($q) => $q->where('session_id', $setting->id))
            ->first();
    }
}
