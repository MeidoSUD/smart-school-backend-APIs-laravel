<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OnlineExam;
use App\Models\OnlineExamQuestion;
use App\Models\OnlineExamResult;
use App\Models\StudentSession;
use App\Models\Student;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * Converted from CodeIgniter: codelgiterControllers/user/Onlineexam.php
 */
class OnlineExamController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $studentSession = $this->getStudentSession($user);
        
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
        $user = request()->user();
        $studentId = $this->getStudentId($user);
        
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
        
        $user = $request->user();
        $studentId = $this->getStudentId($user);
        
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

    private function getStudentSession($user)
    {
        $studentId = $this->getStudentId($user);
        
        if (!$studentId) {
            return null;
        }
        
        $setting = Setting::where('is_active', 1)->first();
        
        return StudentSession::where('student_id', $studentId)
            ->when($setting, fn($q) => $q->where('session_id', $setting->id))
            ->first();
    }

    private function getStudentId($user)
    {
        if ($user->role === 'student') {
            return $user->user_id;
        } elseif ($user->role === 'parent') {
            $student = Student::where('parent_id', $user->id)->first();
            return $student ? $student->id : null;
        }
        return null;
    }
}