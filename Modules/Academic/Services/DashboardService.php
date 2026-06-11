<?php

namespace Modules\Academic\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Academic\Entities\Homework;
use Modules\Academic\Entities\StudentSession;
use Modules\Academic\Entities\SubmitAssignment;
use Modules\Academic\Entities\Syllabus;
use Modules\Core\Entities\Setting;
use Modules\Core\Entities\User;
use Modules\Operations\Entities\Visitor;
use Modules\Operations\Services\NoticeNotificationService;
use Throwable;

class DashboardService
{
    public function __construct(
        private AttendanceCalculator $attendanceCalculator,
        private NoticeNotificationService $noticeNotifications,
    ) {}

    public function build(User $user, StudentSession $studentSession): array
    {
        $setting = Setting::first();
        $class = $studentSession->class;
        $section = $studentSession->section;

        return [
            'attendence_percentage' => $this->attendanceCalculator->percentageForSession(
                $studentSession->id,
                $setting
            ),
            'studentsession_username' => $user->username,
            'student_data' => [
                'id' => $user->id,
                'username' => $user->username,
                'role' => $user->role,
                'student_id' => $studentSession->student_id,
                'class' => $class ? $class->class : null,
                'section' => $section ? $section->section : null,
            ],
            'low_attendance_limit' => $setting ? ($setting->low_attendance_limit ?? 75) : 75,
            'homeworklist' => $this->safe(fn () => $this->homeworkList($studentSession)),
            'notificationlist' => $this->safe(fn () => $this->notifications($user)),
            'subjects_data' => $this->safe(fn () => $this->syllabusSummary($studentSession)),
            'timetable' => $this->safe(fn () => $this->timetableByDay($studentSession)),
            'visitor_list' => $this->safe(fn () => $this->visitors($studentSession->id)),
            'teachers' => $this->safe(fn () => $this->teachers($studentSession)),
        ];
    }

    private function safe(callable $callback): array
    {
        try {
            return $callback();
        } catch (Throwable) {
            return [];
        }
    }

    private function homeworkList(StudentSession $studentSession): array
    {
        if (!Schema::hasTable('homework')) {
            return [];
        }

        $homeworklist = Homework::where('class_id', $studentSession->class_id)
            ->where('section_id', $studentSession->section_id)
            ->where('submit_date', '>=', now()->toDateString())
            ->get();

        return $homeworklist->map(function ($homework) use ($studentSession) {
            $submitted = Schema::hasTable('submit_assignment')
                && SubmitAssignment::where('homework_id', $homework->id)
                    ->where('student_id', $studentSession->student_id)
                    ->exists();

            $row = $homework->toArray();
            $row['status'] = $submitted ? 'submitted' : '';

            return $row;
        })->values()->all();
    }

    private function notifications(User $user): array
    {
        return $this->noticeNotifications->listForUser($user);
    }

    private function syllabusSummary(StudentSession $studentSession): array
    {
        if (!Schema::hasTable('subject_group_subjects')) {
            return [];
        }

        $subjects = Syllabus::getMySubjects(
            $studentSession->class_id,
            $studentSession->section_id,
            $studentSession->session_id
        );

        $result = [];
        foreach ($subjects as $subject) {
            $subjectDetails = Syllabus::getSubjectStatus(
                $subject->subject_group_subjects_id,
                $subject->subject_group_class_sections_id
            );

            $total = $subjectDetails->total ?? 0;
            $completePercent = 0;
            $incompletePercent = 0;

            if ($total > 0) {
                $completePercent = round(($subjectDetails->complete / $total) * 100);
                $incompletePercent = round(($subjectDetails->incomplete / $total) * 100);
            }

            $label = $subject->code ? $subject->name . ' (' . $subject->code . ')' : $subject->name;

            $result[$subject->subject_group_subjects_id] = [
                'lebel' => $label,
                'complete' => $completePercent,
                'incomplete' => $incompletePercent,
                'id' => $subject->subject_group_subjects_id . '_' . $subject->code,
                'total' => $total,
                'name' => $subject->name,
                'graph_id' => $subject->subject_group_subjects_id . time(),
            ];
        }

        return $result;
    }

    private function timetableByDay(StudentSession $studentSession): array
    {
        if (!Schema::hasTable('subject_timetable')) {
            return [];
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
            $result[$row->day][] = [
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

        return $result;
    }

    private function visitors(int $studentSessionId): array
    {
        if (!Schema::hasTable('visitors_book')) {
            return [];
        }

        return Visitor::where('student_session_id', $studentSessionId)
            ->orderByDesc('id')
            ->limit(10)
            ->get()
            ->values()
            ->all();
    }

    private function teachers(StudentSession $studentSession): array
    {
        if (!Schema::hasTable('teacher_subjects')) {
            return [];
        }

        $classSectionId = DB::table('class_sections')
            ->where('class_id', $studentSession->class_id)
            ->where('section_id', $studentSession->section_id)
            ->value('id');

        if (!$classSectionId) {
            return [];
        }

        return DB::table('teacher_subjects')
            ->join('staff', 'staff.id', '=', 'teacher_subjects.teacher_id')
            ->where('teacher_subjects.class_section_id', $classSectionId)
            ->where('teacher_subjects.session_id', $studentSession->session_id)
            ->select('staff.*')
            ->distinct()
            ->get()
            ->values()
            ->all();
    }
}
