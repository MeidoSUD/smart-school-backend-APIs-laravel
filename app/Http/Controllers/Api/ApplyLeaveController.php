<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApplyLeave;
use App\Models\StudentSession;
use App\Models\Student;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;

/**
 * Converted from CodeIgniter: codelgiterControllers/user/Apply_leave.php
 */
class ApplyLeaveController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $studentSession = $this->getStudentSession($user);
        
        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }
        
        $studentId = $this->getStudentId($user);
        $student = Student::find($studentId);
        
        $results = ApplyLeave::where('student_session_id', $studentSession->id)
            ->orderBy('apply_date', 'desc')
            ->get();
        
        $studentClasses = StudentSession::where('student_id', $studentId)->with(['class', 'section'])->get();
        
        $data = [
            'results' => $results,
            'studentclasses' => $studentClasses,
        ];
        
        return $this->successResponse($data);
    }

    public function get_details($id): JsonResponse
    {
        $data = ApplyLeave::find($id);
        
        if (!$data) {
            return $this->errorResponse('Leave not found', null, 404);
        }
        
        $data->from_date = Carbon::parse($data->from_date)->format('d-m-Y');
        $data->to_date = Carbon::parse($data->to_date)->format('d-m-Y');
        $data->apply_date = Carbon::parse($data->apply_date)->format('d-m-Y');
        
        return $this->successResponse($data);
    }

    public function add(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'apply_date' => 'required',
            'from_date' => 'required',
            'to_date' => 'required',
            'message' => 'required|string',
        ]);
        
        $user = $request->user();
        $studentSession = $this->getStudentSession($user);
        
        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }
        
        $data = [
            'apply_date' => Carbon::parse($request->apply_date)->format('Y-m-d'),
            'from_date' => Carbon::parse($request->from_date)->format('Y-m-d'),
            'to_date' => Carbon::parse($request->to_date)->format('Y-m-d'),
            'student_session_id' => $studentSession->id,
            'reason' => $request->message,
        ];
        
        $leaveId = $request->leave_id;
        
        if ($leaveId) {
            $data['id'] = $leaveId;
            ApplyLeave::where('id', $leaveId)->update($data);
        } else {
            $leave = ApplyLeave::create($data);
            $leaveId = $leave->id;
        }
        
        $document = null;
        if ($request->hasFile('files')) {
            $file = $request->file('files')[0];
            $document = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/student_leavedocuments'), $document);
            ApplyLeave::where('id', $leaveId)->update(['docs' => $document]);
        }
        
        return $this->successResponse(['leave_id' => $leaveId], 'Leave application submitted successfully');
    }

    public function remove_leave($id): JsonResponse
    {
        $row = ApplyLeave::find($id);
        
        if ($row && $row->docs) {
            $filePath = public_path('uploads/student_leavedocuments/' . $row->docs);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
        
        ApplyLeave::destroy($id);
        
        return $this->successResponse(null, 'Leave removed successfully');
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