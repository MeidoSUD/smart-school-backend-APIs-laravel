<?php

namespace Modules\Academic\Http\Controllers\Api;

use Modules\Academic\Entities\ClassTimetable;
use Modules\Academic\Entities\StudentSession;
use Modules\Academic\Entities\Student;
use Modules\Academic\Entities\SubjectGroup;
use Modules\Academic\Entities\SubjectGroupSubject;
use Modules\Academic\Entities\Classe;
use Modules\Academic\Entities\Section;
use Modules\Core\Entities\Session;
use Modules\Staff\Entities\Staff;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Core\Traits\HasStudentSession;

class TimetableController extends \Modules\Core\Http\Controllers\Api\Controller
{
    use HasStudentSession;

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

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'subject_group_id' => 'required|exists:subject_groups,id',
            'subject_group_subject_id' => 'required|exists:subject_group_subjects,id',
            'staff_id' => 'required|exists:staff,id',
            'day' => 'required|string|in:Sunday,Monday,Tuesday,Wednesday,Thursday',
            'time_from' => 'required|string',
            'time_to' => 'required|string',
            'room_no' => 'nullable|string|max:20',
            'is_active' => 'sometimes|in:yes,no,1,0',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first());
        }

        $session = Session::where('is_active', 'yes')->first();
        if (!$session) {
            return $this->errorResponse('No active session found');
        }

        $timetable = ClassTimetable::create([
            'session_id' => $session->id,
            'class_id' => $request->class_id,
            'section_id' => $request->section_id,
            'subject_group_id' => $request->subject_group_id,
            'subject_group_subject_id' => $request->subject_group_subject_id,
            'staff_id' => $request->staff_id,
            'day' => $request->day,
            'time_from' => $request->time_from,
            'time_to' => $request->time_to,
            'room_no' => $request->room_no ?? '',
            'is_active' => $request->is_active ?? 'yes',
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);

        return $this->successResponse(['timetable' => $timetable], 'Timetable created successfully');
    }

    public function update(Request $request, $id): JsonResponse
    {
        $timetable = ClassTimetable::find($id);
        if (!$timetable) {
            return $this->errorResponse('Timetable not found');
        }

        $validator = Validator::make($request->all(), [
            'class_id' => 'sometimes|exists:classes,id',
            'section_id' => 'sometimes|exists:sections,id',
            'subject_group_id' => 'sometimes|exists:subject_groups,id',
            'subject_group_subject_id' => 'sometimes|exists:subject_group_subjects,id',
            'staff_id' => 'sometimes|exists:staff,id',
            'day' => 'sometimes|string|in:Sunday,Monday,Tuesday,Wednesday,Thursday',
            'time_from' => 'sometimes|string',
            'time_to' => 'sometimes|string',
            'room_no' => 'nullable|string|max:20',
            'is_active' => 'sometimes|in:yes,no,1,0',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first());
        }

        $timetable->update($request->only([
            'class_id', 'section_id', 'subject_group_id',
            'subject_group_subject_id', 'staff_id', 'day',
            'time_from', 'time_to', 'room_no', 'is_active',
            'start_time', 'end_time',
        ]));

        return $this->successResponse(['timetable' => $timetable], 'Timetable updated successfully');
    }

    public function destroy($id): JsonResponse
    {
        $timetable = ClassTimetable::find($id);
        if (!$timetable) {
            return $this->errorResponse('Timetable not found');
        }

        $timetable->delete();

        return $this->successResponse(null, 'Timetable deleted successfully');
    }
}
