<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Homework;
use App\Models\HomeworkEvaluation;
use App\Models\DailyAssignment;
use App\Models\StudentSession;
use App\Models\Student;
use App\Models\Staff;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use DB;

/**
 * Converted from CodeIgniter: codelgiterControllers/user/Homework.php
 */
class HomeworkController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $studentSession = $this->getStudentSession($user);
        
        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }
        
        $homeworklist = Homework::where('class_id', $studentSession->class_id)
            ->where('section_id', $studentSession->section_id)
            ->where('submission_date', '>=', now())
            ->get();
        
        foreach ($homeworklist as $key => $homework) {
            $checkstatus = HomeworkEvaluation::where('homework_id', $homework->id)
                ->where('student_id', $studentSession->student_id)
                ->count();
            
            $homeworklist[$key]['status'] = $checkstatus > 0 ? 'submitted' : '';
        }
        
        $closedhomeworklist = Homework::where('class_id', $studentSession->class_id)
            ->where('section_id', $studentSession->section_id)
            ->where('submission_date', '<', now())
            ->get();
        
        foreach ($closedhomeworklist as $key => $homework) {
            $checkstatus = HomeworkEvaluation::where('homework_id', $homework->id)
                ->where('student_id', $studentSession->student_id)
                ->count();
            
            $closedhomeworklist[$key]['status'] = $checkstatus > 0 ? 'submitted' : '';
        }
        
        $data = [
            'created_by' => '',
            'evaluated_by' => '',
            'homeworklist' => $homeworklist,
            'closedhomeworklist' => $closedhomeworklist,
        ];
        
        return $this->successResponse($data);
    }

    public function upload_docs(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'homework_id' => 'required',
            'message' => 'required|string',
            'file' => 'nullable|file|max:10240',
        ]);
        
        $user = $request->user();
        $studentId = $this->getStudentId($user);
        
        $homeworkId = $request->homework_id;
        
        $isRequired = HomeworkEvaluation::where('homework_id', $homeworkId)
            ->where('student_id', $studentId)
            ->count();
        
        if ($isRequired == 0 && !$request->hasFile('file')) {
            return $this->errorResponse('File is required');
        }
        
        $data = [
            'homework_id' => $homeworkId,
            'student_id' => $studentId,
            'docs' => '',
            'remark' => $request->message,
            'evaluation_date' => now(),
        ];
        
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/homework/assignment'), $filename);
            $data['docs'] = $filename;
        }
        
        HomeworkEvaluation::create($data);
        
        return $this->successResponse(null, 'Homework submitted successfully');
    }

    public function homework_detail($id, $status): JsonResponse
    {
        $result = Homework::find($id);
        
        if (!$result) {
            return $this->errorResponse('Homework not found', null, 404);
        }
        
        $setting = Setting::first();
        $superadminRestriction = $setting ? ($setting->superadmin_restriction ?? false) : false;
        
        $classId = $result->class_id;
        $sectionId = $result->section_id;
        
        $data = [
            'homework_status' => $status,
            'homework_id' => $id,
            'title' => 'Homework Evaluation',
            'result' => $result,
        ];
        
        return $this->successResponse($data);
    }

    public function download($id): JsonResponse
    {
        $homework = Homework::find($id);
        
        if (!$homework) {
            return $this->errorResponse('Homework not found', null, 404);
        }
        
        return $this->successResponse(['document' => $homework->document]);
    }

    public function dailyassignment(Request $request): JsonResponse
    {
        $user = $request->user();
        $studentSession = $this->getStudentSession($user);
        
        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }
        
        $dailyassignmentlist = DailyAssignment::where('student_session_id', $studentSession->id)
            ->orderBy('date', 'desc')
            ->get();
        
        $data = [
            'dailyassignmentlist' => $dailyassignmentlist,
        ];
        
        return $this->successResponse($data);
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