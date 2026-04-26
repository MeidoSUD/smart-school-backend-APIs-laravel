<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendenceType;
use App\Models\StudentAttendence;
use App\Models\CalendarEvent;
use App\Models\Setting;
use App\Models\StudentSession;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;

/**
 * Converted from CodeIgniter: codelgiterControllers/user/Attendence.php
 */
class AttendenceController extends Controller
{
    public function index(): JsonResponse
    {
        $setting = Setting::first();
        
        $data = [
            'attendence_type' => $setting->attendence_type ?? 'day',
            'language' => session('language') ?? 'english',
        ];
        
        return $this->successResponse($data);
    }

    public function getdaysubattendence(Request $request): JsonResponse
    {
        $date = $request->get('date') ?? date('Y-m-d');
        $date = Carbon::parse($date)->format('Y-m-d');
        
        $attendencetypes = AttendenceType::where('is_active', 'yes')->get();
        
        $user = $request->user();
        $studentSession = $this->getStudentSession($user);
        
        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }
        
        $attendance = StudentAttendence::with('attendenceType')
            ->where('student_session_id', $studentSession->id)
            ->where('date', $date)
            ->get();
        
        $result = [
            'attendencetypeslist' => $attendencetypes,
            'attendence' => $attendance,
        ];
        
        return $this->successResponse($result);
    }

    public function getAttendence(Request $request): JsonResponse
    {
        $start = $request->get('start') ?? date('Y-m-01');
        $end = $request->get('end') ?? date('Y-m-t');
        
        $user = $request->user();
        $studentSession = $this->getStudentSession($user);
        
        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }
        
        $attendance = StudentAttendence::with('attendenceType')
            ->where('student_session_id', $studentSession->id)
            ->whereBetween('date', [$start, $end])
            ->orderBy('date', 'asc')
            ->get();
        
        $eventdata = [];
        foreach ($attendance as $row) {
            $color = '#27ab00';
            if ($row->attendenceType && $row->attendenceType->type == 'Absent') {
                $color = '#fa2601';
            } elseif ($row->attendenceType && $row->attendenceType->type == 'Late') {
                $color = '#ffeb00';
            } elseif ($row->attendenceType && $row->attendenceType->type == 'Holiday') {
                $color = '#a7a7a7';
            } elseif ($row->attendenceType && $row->attendenceType->type == 'Half Day') {
                $color = '#fa8a00';
            }
            
            $eventdata[] = [
                'title' => $row->attendenceType ? $row->attendenceType->type : 'Unknown',
                'start' => $row->date,
                'end' => $row->date,
                'description' => $row->remark,
                'backgroundColor' => $color,
                'borderColor' => $color,
                'event_type' => $row->attendenceType ? $row->attendenceType->type : 'Unknown',
            ];
        }
        
        return $this->successResponse($eventdata);
    }

    public function getevents(): JsonResponse
    {
        $result = CalendarEvent::where('status', 'yes')
            ->where('event_type', '!=', 'private')
            ->get();
        
        $eventdata = [];
        foreach ($result as $value) {
            $eventdata[] = [
                'title' => $value->event_title,
                'start' => $value->start_date,
                'end' => $value->end_date,
                'description' => $value->event_description,
                'id' => $value->id,
                'backgroundColor' => $value->event_color,
                'borderColor' => $value->event_color,
                'event_type' => $value->event_type,
            ];
        }
        
        return $this->successResponse($eventdata);
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