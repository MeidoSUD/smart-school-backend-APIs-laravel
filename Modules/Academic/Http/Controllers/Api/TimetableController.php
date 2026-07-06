<?php

namespace Modules\Academic\Http\Controllers\Api;

use Modules\Academic\Entities\ClassTimetable;
use Modules\Academic\Entities\StudentSession;
use Modules\Academic\Entities\Student;
use Modules\Core\Entities\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use DB;

class TimetableController extends \Modules\Core\Http\Controllers\Api\Controller
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

        $timetable = ClassTimetable::where('class_id', $studentSession->class_id)
            ->where('section_id', $studentSession->section_id)
            ->where('session_id', $studentSession->session_id)
            ->with('subjectGroupSubject.subjectGroup.subjects')
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

            $subjectName = 'N/A';
            $subjectCode = '';
            if ($row->subjectGroupSubject && $row->subjectGroupSubject->subjectGroup && $row->subjectGroupSubject->subjectGroup->subjects) {
                $subject = $row->subjectGroupSubject->subjectGroup->subjects->first();
                if ($subject) {
                    $subjectName = $subject->name;
                    $subjectCode = $subject->code;
                }
            }

            $result[$day][] = [
                'id' => $row->id,
                'subject' => $subjectName,
                'subject_code' => $subjectCode,
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

        $setting = Setting::where('is_active', 'yes')->first();

        return StudentSession::where('student_id', $studentId)
            ->when($setting, fn($q) => $q->where('session_id', $setting->id))
            ->first();
    }
}
