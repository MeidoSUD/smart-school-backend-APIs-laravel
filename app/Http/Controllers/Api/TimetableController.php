<?php

namespace App\Http\Controllers\Api;

 
use App\Models\ClassTimetable;
use App\Models\StudentSession;
use App\Models\Student;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use DB;

/**
 * Converted from CodeIgniter: codelgiterControllers/user/Timetable.php
 */
class TimetableController extends Controller
{
    public function __construct()
    {
        $this->setControllerName('TimetableController');
        }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $studentSession = $this->getStudentSession($user);
        
        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
            }


        
        $classSection = DB::table('class_sections')
            ->where('class_id', $studentSession->class_id)
            ->where('section_id', $studentSession->section_id)
            ->first();
        
        if (!$classSection) {
            return $this->successResponse(['timetable' => []], 'No timetable found');
            }


        
        $timetable = ClassTimetable::where('class_section_id', $classSection->id)
            ->where('session_id', $studentSession->session_id)
            ->with('subject')
            ->with('staff')
            ->orderBy('day')
            ->orderBy('time_from')
            ->get();
        
        $result = [];
        foreach ($timetable as $row) {
            $day = $row->day;
            if (!isset($result[$day])) {
                $result[$day] = [];
                }


            $result[$day][] = [
                'id' => $row->id,
                'subject' => $row->subject ? $row->subject->name : 'N/A',
                'subject_code' => $row->subject ? $row->subject->code : '',
                'teacher' => $row->staff ? $row->staff->name : 'N/A',
                'time_from' => $row->time_from,
                'time_to' => $row->time_to,
                'room' => $row->room_no ?? '',
                'day' => $row->day,
            ];
            }


        
        return $this->successResponse(['timetable' => $result]);
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
