<?php

namespace Modules\Staff\Http\Controllers\Api\Teacher;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Staff\Services\TeacherScope;

class TeacherDashboardController extends TeacherController
{
    public function __construct()
    {
        $this->setControllerName('TeacherDashboardController');
    }

    public function dashboard(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        $roleId = $ctx->roles[0]->role_id ?? TeacherScope::TEACHER_ROLE_ID;
        $sessionId = $ctx->sessionId;
        $classes = TeacherScope::myClasses($ctx->staff->id, $sessionId);

        $subjectCount = (int) DB::table('teacher_subjects')
            ->where('teacher_id', $ctx->staff->id)
            ->where('session_id', $sessionId)
            ->distinct('subject_id')
            ->count('subject_id');

        $studentCount = 0;
        if (! empty($classes)) {
            $studentQuery = DB::table('student_session')
                ->where('session_id', $sessionId)
                ->where('is_active', 'yes')
                ->where(function ($q) use ($classes) {
                    foreach ($classes as $class) {
                        $q->orWhere(function ($qq) use ($class) {
                            $qq->where('class_id', $class['class_id'])
                                ->where('section_id', $class['section_id']);
                        });
                    }
                });
            $studentCount = (int) $studentQuery->count();
        }

        $totalStudents = (int) DB::table('student_session')
            ->join('students', 'students.id', '=', 'student_session.student_id')
            ->where('student_session.session_id', $sessionId)
            ->where('students.is_active', 'yes')
            ->count();

        $notifications = $this->unreadNotifications($ctx->staff->id, (int) $roleId);
        $attendanceData = $this->todayAttendance($totalStudents, $sessionId);

        return $this->successResponse([
            'role_id' => (int) $roleId,
            'session_id' => $sessionId,
            'staff' => [
                'id' => $ctx->staff->id,
                'employee_id' => $ctx->staff->employee_id,
                'name' => $ctx->staff->name,
                'surname' => $ctx->staff->surname,
                'email' => $ctx->staff->email,
                'image' => $ctx->staff->image,
            ],
            'my_classes_count' => count($classes),
            'my_subjects_count' => $subjectCount,
            'my_students_count' => $totalStudents,
            'unread_notifications' => count($notifications),
            'notifications' => $notifications,
            'total_students' => $totalStudents,
            'attendence_data' => $attendanceData,
            'calendar_events' => $this->calendarEvents((string) $ctx->staff->id, (int) $roleId, $request),
            'my_classes' => $classes,
        ]);
    }

    public function myClasses(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        return $this->successResponse([
            'classes' => TeacherScope::myClasses($ctx->staff->id, $ctx->sessionId),
        ]);
    }

    public function mySubjects(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        $validated = $request->validate([
            'class_id' => 'required|integer',
            'section_id' => 'required|integer',
        ]);

        $sessionId = $ctx->sessionId;

        $classSectionId = (int) DB::table('class_sections')
            ->where('class_id', $validated['class_id'])
            ->where('section_id', $validated['section_id'])
            ->value('id');

        if (! $classSectionId || ! TeacherScope::inScope($ctx->staff->id, $sessionId, $classSectionId)) {
            return $this->errorResponse('Class section is not in your scope', null, 403);
        }

        $subjects = DB::table('teacher_subjects')
            ->join('subjects', 'subjects.id', '=', 'teacher_subjects.subject_id')
            ->where('teacher_subjects.teacher_id', $ctx->staff->id)
            ->where('teacher_subjects.class_section_id', $classSectionId)
            ->when($sessionId, fn ($q) => $q->where('teacher_subjects.session_id', $sessionId))
            ->select('subjects.id as subject_id', 'subjects.name', 'subjects.code', 'subjects.type')
            ->distinct()
            ->get();

        return $this->successResponse(['subjects' => $subjects]);
    }

    private function unreadNotifications(int $staffId, int $roleId): array
    {
        try {
            return DB::table('send_notification')
                ->join('notification_roles', 'notification_roles.send_notification_id', '=', 'send_notification.id')
                ->leftJoin('read_notification', function ($join) use ($staffId) {
                    $join->on('read_notification.notification_id', '=', 'send_notification.id')
                        ->where('read_notification.staff_id', $staffId);
                })
                ->where('send_notification.visible_staff', 'yes')
                ->whereNull('read_notification.id')
                ->where('notification_roles.role_id', $roleId)
                ->select('send_notification.*')
                ->orderByDesc('send_notification.id')
                ->get()
                ->toArray();
        } catch (\Throwable) {
            return [];
        }
    }

    private function todayAttendance(int $totalStudents, int $sessionId): array
    {
        try {
            $today = date('Y-m-d');
            $divisor = max($totalStudents, 1);
            $row = DB::table('student_attendences')
                ->join('student_session', 'student_attendences.student_session_id', '=', 'student_session.id')
                ->whereDate('student_attendences.date', $today)
                ->where('student_session.session_id', $sessionId)
                ->selectRaw("
                    concat(round((sum(case when attendence_type_id=1 then 1 else 0 end)*100/{$divisor}),2),'%') as present,
                    concat(round((sum(case when attendence_type_id=3 then 1 else 0 end)*100/{$divisor}),2),'%') as late,
                    concat(round((sum(case when attendence_type_id=4 then 1 else 0 end)*100/{$divisor}),2),'%') as absent,
                    concat(round((sum(case when attendence_type_id=6 then 1 else 0 end)*100/{$divisor}),2),'%') as half_day,
                    sum(case when attendence_type_id=1 then 1 else 0 end) as total_present,
                    sum(case when attendence_type_id=3 then 1 else 0 end) as total_late,
                    sum(case when attendence_type_id=4 then 1 else 0 end) as total_absent,
                    sum(case when attendence_type_id=6 then 1 else 0 end) as total_half_day
                ")
                ->first();

            return $row ? (array) $row : [
                'present' => null,
                'late' => null,
                'absent' => null,
                'half_day' => null,
                'total_present' => 0,
                'total_late' => 0,
                'total_absent' => 0,
                'total_half_day' => 0,
            ];
        } catch (\Throwable) {
            return [
                'present' => null,
                'late' => null,
                'absent' => null,
                'half_day' => null,
                'total_present' => 0,
                'total_late' => 0,
                'total_absent' => 0,
                'total_half_day' => 0,
            ];
        }
    }

    private function calendarEvents(string $userId, int $roleId, Request $request): array
    {
        try {
            $query = DB::table('events')
                ->where(function ($q) use ($userId, $roleId) {
                    $q->where(function ($qq) use ($userId) {
                        $qq->where('event_type', 'private')->where('event_for', $userId);
                    })->orWhere(function ($qq) use ($roleId) {
                        $qq->where('event_type', 'sameforall')->where('event_for', (string) $roleId);
                    })->orWhere(function ($qq) {
                        $qq->where('event_type', 'public')->where('event_for', '0');
                    })->orWhere(function ($qq) {
                        $qq->where('event_type', 'protected');
                    })->orWhere(function ($qq) use ($userId, $roleId) {
                        $qq->where('event_type', 'task')->where('event_for', $userId)->where('role_id', $roleId);
                    });
                });

            if ($request->filled('start') && $request->filled('end')) {
                $start = date('Y-m-d 00:00:00', strtotime($request->input('start')));
                $end = date('Y-m-d 23:59:59', strtotime($request->input('end')));
                $query->where(function ($q) use ($start, $end) {
                    $q->where('end_date', '>=', $start)
                        ->where('start_date', '<=', $end);
                });
            } elseif ($request->filled('month')) {
                $year = $request->filled('year') ? (int) $request->input('year') : (int) date('Y');
                $month = (int) $request->input('month');
                $start = sprintf('%04d-%02d-01 00:00:00', $year, $month);
                $end = date('Y-m-t 23:59:59', strtotime($start));
                $query->where(function ($q) use ($start, $end) {
                    $q->where('end_date', '>=', $start)
                        ->where('start_date', '<=', $end);
                });
            }

            return $query->orderBy('id')
                ->get()
                ->map(fn ($row) => [
                    'title' => $row->event_title,
                    'start' => $row->start_date,
                    'end' => $row->end_date,
                    'description' => $row->event_description,
                    'id' => (string) $row->id,
                    'backgroundColor' => $row->event_color,
                    'borderColor' => $row->event_color,
                    'event_type' => $row->event_type,
                ])
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }
}
