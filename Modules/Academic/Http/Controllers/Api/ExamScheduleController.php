<?php

namespace Modules\Academic\Http\Controllers\Api;

use Modules\Academic\Entities\ExamGroupStudent;
use Modules\Academic\Entities\StudentSession;
use Modules\Academic\Entities\Student;
use Modules\Core\Entities\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExamScheduleController extends \Modules\Core\Http\Controllers\Api\Controller
{
    public function __construct()
    {
        $this->setControllerName('ExamScheduleController');
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $studentSession = $this->getStudentSession($user);

        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }

        $examSchedule = ExamGroupStudent::where('student_session_id', $studentSession->id)
            ->with('examGroup')
            ->get();

        $data = ['examSchedule' => $examSchedule];

        return $this->successResponse($data);
    }

    public function getexamscheduledetail(Request $request): JsonResponse
    {
        $examId = $request->post('exam_id');

        $subjectList = [];

        return $this->successResponse(['subject_list' => $subjectList]);
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

        $setting = Setting::where('is_active', 'yes')->first();

        return StudentSession::where('student_id', $studentId)
            ->when($setting, fn($q) => $q->where('session_id', $setting->session_id))
            ->first();
    }
}
