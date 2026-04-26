<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExamGroupStudent;
use App\Models\StudentSession;
use App\Models\Student;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Converted from CodeIgniter: codelgiterControllers/user/Examschedule.php
 */
class ExamScheduleController extends Controller
{
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
        
        $setting = Setting::where('is_active', 1)->first();
        
        return StudentSession::where('student_id', $studentId)
            ->when($setting, fn($q) => $q->where('session_id', $setting->id))
            ->first();
    }
}