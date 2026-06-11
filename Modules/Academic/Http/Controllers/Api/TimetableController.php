<?php

namespace Modules\Academic\Http\Controllers\Api;

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
        $studentSession = $this->studentSession($request);

        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }

        $timetable = DB::table('subject_timetable')
            ->leftJoin('subject_group_subjects', 'subject_group_subjects.id', '=', 'subject_timetable.subject_group_subject_id')
            ->leftJoin('subjects', 'subjects.id', '=', 'subject_group_subjects.subject_id')
            ->leftJoin('staff', 'staff.id', '=', 'subject_timetable.staff_id')
            ->where('subject_timetable.class_id', $studentSession->class_id)
            ->where('subject_timetable.section_id', $studentSession->section_id)
            ->where('subject_timetable.session_id', $studentSession->session_id)
            ->select(
                'subject_timetable.*',
                'subjects.name as subject_name',
                'subjects.code as subject_code',
                'staff.name as teacher_name'
            )
            ->orderBy('subject_timetable.day')
            ->orderBy('subject_timetable.time_from')
            ->get();

        $result = [];
        foreach ($timetable as $row) {
            $day = $row->day;
            if (!isset($result[$day])) {
                $result[$day] = [];
            }

            $result[$day][] = [
                'id' => $row->id,
                'subject' => $row->subject_name ?? 'N/A',
                'subject_code' => $row->subject_code ?? '',
                'teacher' => $row->teacher_name ?? 'N/A',
                'time_from' => $row->time_from,
                'time_to' => $row->time_to,
                'room' => $row->room_no ?? '',
                'day' => $row->day,
            ];
        }

        return $this->successResponse(['timetable' => $result]);
    }

}
