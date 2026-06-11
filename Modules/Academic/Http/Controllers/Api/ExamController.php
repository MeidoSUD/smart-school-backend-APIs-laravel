<?php

namespace Modules\Academic\Http\Controllers\Api;

use Modules\Academic\Entities\Exam;
use Modules\Academic\Entities\ExamSchedule;
use Modules\Academic\Entities\ExamGroupStudent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExamController extends \Modules\Core\Http\Controllers\Api\Controller
{
    public function __construct()
    {
        $this->setControllerName('ExamController');
    }

    public function index(Request $request): JsonResponse
    {
        $studentSession = $this->studentSession($request);

        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }

        $examResult = ExamSchedule::getExamsByClassAndSection(
            $studentSession->class_id,
            $studentSession->section_id,
            $studentSession->session_id
        );

        $data = [
            'class_id' => $studentSession->class_id,
            'section_id' => $studentSession->section_id,
            'examlist' => $examResult,
        ];

        return $this->successResponse($data);
    }

    public function view($id): JsonResponse
    {
        $exam = Exam::find($id);

        if (!$exam) {
            return $this->errorResponse('Exam not found', null, 404);
        }

        return $this->successResponse(['exam' => $exam]);
    }

    public function examresult(Request $request): JsonResponse
    {
        $studentSession = $this->studentSession($request);

        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }

        $examResult = ExamGroupStudent::where('student_session_id', $studentSession->id)
            ->with('examGroup')
            ->get();

        $data = [
            'exam_result' => $examResult,
        ];

        return $this->successResponse($data);
    }

}
