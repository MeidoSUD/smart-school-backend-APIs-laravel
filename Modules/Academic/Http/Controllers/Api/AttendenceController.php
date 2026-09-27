<?php

namespace Modules\Academic\Http\Controllers\Api;

use Modules\Academic\Entities\AttendenceType;
use Modules\Academic\Entities\StudentAttendence;
use Modules\Academic\Entities\CalendarEvent;
use Modules\Core\Services\SchoolSettingsService;
use Modules\Core\Services\StudentSessionService;
use Dedoc\Scramble\Attributes\BodyParameter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendenceController extends \Modules\Core\Http\Controllers\Api\Controller
{
    public function __construct(
        private readonly StudentSessionService $studentSessionService,
        private readonly SchoolSettingsService $schoolSettingsService
    ) {
        $this->setControllerName('AttendenceController');
    }

    public function index(): JsonResponse
    {
        // CI: User/Attendence::index() branches on sch_settings.attendence_type
        // (truthy = subject-wise view, falsy = day-wise calendar) and exposes the
        // student language short code for the fullcalendar locale.
        $setting = $this->schoolSettingsService->getSettings();

        $attendenceType = (int) ($setting->getAttribute('attendence_type') ?? 0);
        $shortCode = 'en';
        try {
            $langId = $setting->getAttribute('lang_id');
            if ($langId) {
                $shortCode = DB::table('languages')->where('id', $langId)->value('short_code') ?? 'en';
            }
        } catch (\Throwable $e) {
            $shortCode = 'en';
        }

        $data = [
            // CI-compatible: 0 = day-wise, 1 = subject-wise.
            'attendence_type' => $attendenceType,
            'language' => $shortCode,
            'language_shortcode' => $shortCode,
            'date_format' => $setting->getAttribute('date_format') ?? 'd-m-Y',
        ];

        return $this->successResponse($data);
    }

    #[BodyParameter('date', description: 'Date to check attendance. Accepts Y-m-d or the school date_format (CI datetostrtotime compatible). Defaults to today.', type: 'string', example: '2024-01-15')]
    public function getdaysubattendence(Request $request): JsonResponse
    {
        // CI: User/Attendence::getdaysubattendence() reads POST date in the school
        // date_format via customlib->datetostrtotime(), derives the weekday name,
        // then calls Studentsubjectattendence_model::studentAttendanceByDate().
        $rawDate = $request->input('date', date('Y-m-d'));
        $date = $this->parseCiDate((string) $rawDate);
        $day = Carbon::parse($date)->format('l'); // Monday..Sunday = subject_timetable.day

        $attendencetypes = AttendenceType::where('is_active', 'yes')->orderBy('id')->get();

        $user = $request->user();
        $studentSession = $this->studentSessionService->getStudentSession($user);

        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }

        // Replicates CI studentAttendanceByDate(): timetable rows for the
        // class/section weekday (current session) LEFT JOINed to this student's
        // marking for the exact date. The CI `AND date=` predicate on the
        // LEFT JOINed table means days with no marking return zero rows.
        $rows = DB::table('subject_timetable')
            ->join('subject_group_subjects', function ($join) use ($studentSession) {
                $join->on('subject_group_subjects.id', '=', 'subject_timetable.subject_group_subject_id')
                    ->where('subject_group_subjects.session_id', '=', $studentSession->session_id);
            })
            ->join('subjects', 'subjects.id', '=', 'subject_group_subjects.subject_id')
            ->leftJoin('student_subject_attendances', function ($join) use ($studentSession, $date) {
                $join->on('student_subject_attendances.subject_timetable_id', '=', 'subject_timetable.id')
                    ->where('student_subject_attendances.student_session_id', '=', $studentSession->id)
                    ->where('student_subject_attendances.date', '=', $date);
            })
            ->leftJoin('attendence_type', 'attendence_type.id', '=', 'student_subject_attendances.attendence_type_id')
            ->where('subject_timetable.class_id', $studentSession->class_id)
            ->where('subject_timetable.section_id', $studentSession->section_id)
            ->where('subject_timetable.day', $day)
            ->where('student_subject_attendances.date', $date)
            ->orderBy('subject_timetable.time_from')
            ->select([
                'subject_timetable.id as subject_timetable_id',
                'subjects.id as subject_id',
                'subjects.name as subject_name',
                'subjects.code as subject_code',
                'subjects.type as subject_type',
                'subject_timetable.time_from',
                'subject_timetable.time_to',
                'subject_timetable.room_no',
                'subject_timetable.day',
                'student_subject_attendances.id as student_subject_attendance_id',
                'student_subject_attendances.student_session_id',
                'student_subject_attendances.attendence_type_id',
                'student_subject_attendances.date',
                'student_subject_attendances.remark',
                'attendence_type.type as attendence_type',
                'attendence_type.key_value',
            ])
            ->get();

        // CI view _getdaysubattendence.php: empty -> "no_record_found" alert;
        // otherwise one row per subject with N/A badge when attendence_type_id=="".
        $attendence = $rows->map(function ($row) {
            $unmarked = $row->attendence_type_id === null || (string) $row->attendence_type_id === '';
            return [
                'subject_timetable_id' => $row->subject_timetable_id,
                'subject_id' => $row->subject_id,
                'subject_name' => $row->subject_name,
                // CI renders "name (code)".
                'subject' => $row->subject_name . ' (' . $row->subject_code . ')',
                'subject_code' => $row->subject_code,
                'time_from' => $row->time_from,
                'time_to' => $row->time_to,
                'room_no' => $row->room_no,
                'day' => $row->day,
                'date' => $row->date ? Carbon::parse($row->date)->format('Y-m-d') : null,
                'remark' => $row->remark,
                'attendence_type_id' => $unmarked ? '' : $row->attendence_type_id,
                'attendence_type' => $row->attendence_type,
                'key_value' => $unmarked ? null : $row->key_value,
                'is_marked' => !$unmarked,
            ];
        })->values();

        $result = [
            'date' => $date,
            'day' => $day,
            'attendencetypeslist' => $attendencetypes,
            'attendence' => $attendence,
        ];

        return $this->successResponse($result);
    }

    /**
     * CI Customlib::datetostrtotime() compatible parser: accepts Y-m-d plus the
     * school date_format variants (d-m-Y, d/m/Y, d-M-Y, d.m.Y, m-d-Y, m/d/Y,
     * m.d.Y, Y/m/d). Falls back to today on empty/unparseable input.
     */
    private function parseCiDate(string $raw): string
    {
        $raw = trim($raw);
        if ($raw === '' || strtotime($raw) === false) {
            // Try explicit CI formats before giving up.
            foreach (['d-m-Y', 'd/m/Y', 'd-M-Y', 'd.m.Y', 'm-d-Y', 'm/d/Y', 'm.d.Y', 'Y/m/d', 'Y-m-d'] as $format) {
                try {
                    return Carbon::createFromFormat($format, $raw)->format('Y-m-d');
                } catch (\Throwable $e) {
                    continue;
                }
            }
            return date('Y-m-d');
        }
        try {
            return Carbon::parse($raw)->format('Y-m-d');
        } catch (\Throwable $e) {
            return date('Y-m-d');
        }
    }

    #[BodyParameter('start', description: 'Start date (Y-m-d). Defaults to first day of month.', type: 'string', example: '2024-01-01')]
    #[BodyParameter('end', description: 'End date (Y-m-d). Defaults to last day of month.', type: 'string', example: '2024-01-31')]
    public function getAttendence(Request $request): JsonResponse
    {
        // CI: User/Attendence::getAttendence() reads GET start/end from
        // fullCalendar and returns one colored event per student_attendences row.
        $start = $this->parseCiDate((string) ($request->query('start', $request->input('start', date('Y-m-01')))));
        $end = $this->parseCiDate((string) ($request->query('end', $request->input('end', date('Y-m-t')))));
        if ($start > $end) {
            [$start, $end] = [$end, $start];
        }

        $user = $request->user();
        $studentSession = $this->studentSessionService->getStudentSession($user);

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
            // CI color map (Attendence::getAttendence): Present #27ab00,
            // Absent #fa2601, Late + Late with excuse #ffeb00,
            // Holiday #a7a7a7, Half Day #fa8a00.
            $type = $row->attendenceType ? $row->attendenceType->type : 'Unknown';
            $color = '#27ab00';
            if ($type == 'Absent') {
                $color = '#fa2601';
            } elseif ($type == 'Late' || $type == 'Late with excuse') {
                $color = '#ffeb00';
            } elseif ($type == 'Holiday') {
                $color = '#a7a7a7';
            } elseif ($type == 'Half Day') {
                $color = '#fa8a00';
            }

            $eventdata[] = [
                'title' => $type,
                'start' => $row->date instanceof \DateTimeInterface ? $row->date->format('Y-m-d') : (string) $row->date,
                'end' => $row->date instanceof \DateTimeInterface ? $row->date->format('Y-m-d') : (string) $row->date,
                'description' => $row->remark,
                'id' => 0,
                'backgroundColor' => $color,
                'borderColor' => $color,
                'event_type' => $type,
            ];
        }

        return $this->successResponse($eventdata);
    }

    public function getevents(): JsonResponse
    {
        $result = CalendarEvent::where('is_active', 'yes')
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
}
