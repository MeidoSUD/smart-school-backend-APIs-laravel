<?php

namespace Modules\Academic\Http\Controllers\Api;

use Modules\Academic\Entities\OnlineExam;
use Modules\Academic\Entities\OnlineExamQuestion;
use Modules\Academic\Entities\OnlineExamResult;
use Modules\Academic\Entities\StudentSession;
use Modules\Academic\Entities\Student;
use Modules\Core\Entities\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class OnlineExamController extends \Modules\Core\Http\Controllers\Api\Controller
{
    public function __construct()
    {
        $this->setControllerName('OnlineExamController');
    }

    public function index(Request $request): JsonResponse
    {
        $studentSession = $this->studentSession($request);

        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }

        $student = Student::find($studentSession->student_id);

        $examList = OnlineExam::where('class_id', $studentSession->class_id)
            ->where('section_id', $studentSession->section_id)
            ->where('is_active', 1)
            ->get();

        $data = [
            'student' => $student,
            'examList' => $examList,
        ];

        return $this->successResponse($data);
    }

    public function exam_detail($id): JsonResponse
    {
        $studentId = $this->resolvedStudentId(request());

        $result = OnlineExam::find($id);

        if (!$result) {
            return $this->errorResponse('Exam not found', null, 404);
        }

        $questions = OnlineExamQuestion::where('online_exam_id', $id)->get();

        $data = [
            'result' => $result,
            'questions' => $questions,
        ];

        return $this->successResponse($data);
    }

    public function submit(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'onlineexam_id' => 'required',
            'answers' => 'required',
        ]);

        $studentId = $this->resolvedStudentId($request);

        $answers = is_string($request->answers) ? json_decode($request->answers, true) : $request->answers;

        $result = OnlineExamResult::create([
            'online_exam_id' => $request->onlineexam_id,
            'student_id' => $studentId,
            'answers' => json_encode($answers),
            'attended_on' => now(),
            'is_active' => 1,
        ]);

        return $this->successResponse(['result' => $result], 'Exam submitted successfully');
    }


}
