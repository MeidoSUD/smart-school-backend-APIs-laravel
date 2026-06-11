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
        $studentSession = $this->studentSession($request);

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

}
