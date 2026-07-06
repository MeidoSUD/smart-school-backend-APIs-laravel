<?php

namespace Modules\Academic\Http\Controllers\Api;

use Modules\Academic\Entities\ClassTimetable;
use Modules\Core\Entities\Session;
use Modules\Core\Services\StudentSessionService;
use Modules\Staff\Entities\Staff;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TimetableController extends \Modules\Core\Http\Controllers\Api\Controller
{
    public function __construct(
        private readonly StudentSessionService $studentSessionService
    ) {
        $this->setControllerName('TimetableController');
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (in_array($user->role, ['student', 'parent'])) {
            $studentSession = $this->studentSessionService->getStudentSession($user);

            if (!$studentSession) {
                return $this->errorResponse('Student session not found');
            }

            $timetable = ClassTimetable::select(
                'subject_timetable.*',
                'subjects.name as subject_name',
                'subjects.code as subject_code',
                'staff.name as teacher_name'
            )
                ->leftJoin('subject_group_subjects', 'subject_timetable.subject_group_subject_id', '=', 'subject_group_subjects.id')
                ->leftJoin('subjects', 'subjects.id', '=', 'subject_group_subjects.subject_id')
                ->leftJoin('staff', 'staff.id', '=', 'subject_timetable.staff_id')
                ->where('subject_timetable.class_id', $studentSession->class_id)
                ->where('subject_timetable.section_id', $studentSession->section_id)
                ->orderBy('subject_timetable.day')
                ->orderBy('subject_timetable.time_from')
                ->get();
        } elseif (in_array($user->role, ['teacher', 'staff'])) {
            $staff = Staff::where('user_id', $user->id)->where('is_active', 1)->first();

            if (!$staff) {
                return $this->errorResponse('Staff record not found');
            }

            $timetable = ClassTimetable::select(
                'subject_timetable.*',
                'subjects.name as subject_name',
                'subjects.code as subject_code',
                'staff.name as teacher_name',
                'classes.class as class_name',
                'sections.section as section_name'
            )
                ->leftJoin('subject_group_subjects', 'subject_timetable.subject_group_subject_id', '=', 'subject_group_subjects.id')
                ->leftJoin('subjects', 'subjects.id', '=', 'subject_group_subjects.subject_id')
                ->leftJoin('staff', 'staff.id', '=', 'subject_timetable.staff_id')
                ->leftJoin('classes', 'classes.id', '=', 'subject_timetable.class_id')
                ->leftJoin('sections', 'sections.id', '=', 'subject_timetable.section_id')
                ->where('subject_timetable.staff_id', $staff->id)
                ->orderBy('subject_timetable.day')
                ->orderBy('subject_timetable.time_from')
                ->get();
        } else {
            return $this->errorResponse('Unauthorized role');
        }

        $result = [];
        foreach ($timetable as $row) {
            $day = $row->day;
            if (!isset($result[$day])) {
                $result[$day] = [];
            }

            $entry = [
                'id' => $row->id,
                'subject' => $row->subject_name ?? 'N/A',
                'subject_code' => $row->subject_code ?? '',
                'teacher' => $row->teacher_name ?? 'N/A',
                'time_from' => $row->time_from,
                'time_to' => $row->time_to,
                'room' => $row->room_no ?? '',
                'day' => $row->day,
            ];

            if (in_array($user->role, ['teacher', 'staff'])) {
                $entry['class_name'] = $row->class_name ?? '';
                $entry['section_name'] = $row->section_name ?? '';
            }

            $result[$day][] = $entry;
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
