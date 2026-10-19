<?php

namespace Modules\Staff\Http\Controllers\Api\Teacher;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Exceptions\HttpResponseException;
use Modules\Staff\Services\TeacherScope;

/**
 * تحضير المعلم — parity مع:
 * - CI admin/Stuattendence::index (بحث class/section/date + حفظ insert/update + عطلة=5)
 * - CI admin/Stuattendence::attendencereport (تقرير)
 * - CI admin/Subjectattendence (تحضير المواد عبر student_subject_attendances)
 * ملاحظة: إشعار SMS للغائبين (mailsms absent_attendence) غير مُنفذ — يُوثق كفرق مقصود.
 */
class TeacherAttendanceController extends TeacherController
{
    public function __construct()
    {
        $this->setControllerName('TeacherAttendanceController');
    }

    /** resolve class_section ضمن النطاق — يرمي 403 خارج النطاق. */
    private function scopedSection(int $staffId, int $sessionId, int $classId, int $sectionId): int
    {
        $id = (int) DB::table('class_sections')
            ->where('class_id', $classId)->where('section_id', $sectionId)->value('id');
        if (! $id || ! TeacherScope::inScope($staffId, $sessionId, $id)) {
            throw new HttpResponseException(
                $this->errorResponse('Class section is not in your scope', null, 403)
            );
        }

        return $id;
    }

    public function index(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        $v = $request->validate([
            'class_id' => 'required|integer', 'section_id' => 'required|integer',
            'date' => 'required|date',
        ]);
        $csId = $this->scopedSection((int) $ctx->staff->id, $ctx->sessionId, (int) $v['class_id'], (int) $v['section_id']);

        $students = DB::table('students')
            ->join('student_session', 'student_session.student_id', '=', 'students.id')
            ->leftJoin('student_attendences', function ($j) use ($v) {
                $j->on('student_attendences.student_session_id', '=', 'student_session.id')
                    ->whereDate('student_attendences.date', $v['date']);
            })
            ->leftJoin('attendence_type', 'attendence_type.id', '=', 'student_attendences.attendence_type_id')
            ->where('students.is_active', 'yes')
            ->where('student_session.class_id', $v['class_id'])
            ->where('student_session.section_id', $v['section_id'])
            ->when($ctx->sessionId, fn ($q) => $q->where('student_session.session_id', $ctx->sessionId))
            ->select(
                'students.id', 'students.admission_no', 'students.firstname',
                'students.middlename', 'students.lastname',
                'student_session.id as student_session_id',
                'student_attendences.id as attendence_id',
                'student_attendences.attendence_type_id', 'student_attendences.remark',
                'attendence_type.type as attendence_type'
            )
            ->orderBy('students.id')->get();

        $types = DB::table('attendence_type')->select('id', 'type')->get();

        return $this->successResponse(['students' => $students, 'attendence_types' => $types, 'date' => $v['date']]);
    }

    public function store(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        $v = $request->validate([
            'class_id' => 'required|integer', 'section_id' => 'required|integer',
            'date' => 'required|date', 'holiday' => 'nullable|boolean',
            'records' => 'required|array|min:1',
            'records.*.student_session_id' => 'required|integer',
            'records.*.attendence_type_id' => 'required|integer|exists:attendence_type,id',
            'records.*.remark' => 'nullable|string|max:500',
        ]);
        $csId = $this->scopedSection((int) $ctx->staff->id, $ctx->sessionId, (int) $v['class_id'], (int) $v['section_id']);

        // student_session_ids يجب أن تنتمي لنفس الفصل والجلسة (حماية تجاوز النطاق)
        $validIds = DB::table('student_session')
            ->where('class_id', $v['class_id'])->where('section_id', $v['section_id'])
            ->when($ctx->sessionId, fn ($q) => $q->where('session_id', $ctx->sessionId))
            ->pluck('id')->map(fn ($x) => (int) $x)->all();

        $now = now();
        $inserted = 0; $updated = 0;
        DB::transaction(function () use ($v, $validIds, $now, &$inserted, &$updated) {
            foreach ($v['records'] as $rec) {
                $ssid = (int) $rec['student_session_id'];
                if (! in_array($ssid, $validIds, true)) continue;
                $typeId = ! empty($v['holiday']) ? 5 : (int) $rec['attendence_type_id'];
                $existing = DB::table('student_attendences')
                    ->where('student_session_id', $ssid)->whereDate('date', $v['date'])->first();
                $row = ['student_session_id' => $ssid, 'attendence_type_id' => $typeId,
                    'remark' => $rec['remark'] ?? null, 'date' => $v['date'], 'updated_at' => $now];
                if ($existing) {
                    DB::table('student_attendences')->where('id', $existing->id)->update($row);
                    $updated++;
                } else {
                    $row['created_at'] = $now;
                    DB::table('student_attendences')->insert($row);
                    $inserted++;
                }
            }
        });

        return $this->successResponse(['inserted' => $inserted, 'updated' => $updated], 'Attendance saved successfully');
    }

    public function report(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        $v = $request->validate([
            'class_id' => 'required|integer', 'section_id' => 'required|integer',
            'from' => 'required|date', 'to' => 'required|date|after_or_equal:from',
        ]);
        $csId = $this->scopedSection((int) $ctx->staff->id, $ctx->sessionId, (int) $v['class_id'], (int) $v['section_id']);

        $rows = DB::table('student_attendences')
            ->join('student_session', 'student_session.id', '=', 'student_attendences.student_session_id')
            ->join('students', 'students.id', '=', 'student_session.student_id')
            ->leftJoin('attendence_type', 'attendence_type.id', '=', 'student_attendences.attendence_type_id')
            ->where('student_session.class_id', $v['class_id'])
            ->where('student_session.section_id', $v['section_id'])
            ->when($ctx->sessionId, fn ($q) => $q->where('student_session.session_id', $ctx->sessionId))
            ->whereBetween('student_attendences.date', [$v['from'], $v['to']])
            ->select('students.id as student_id', 'students.admission_no', 'students.firstname',
                'students.lastname', 'attendence_type.type',
                DB::raw('COUNT(*) as days'))
            ->groupBy('students.id', 'students.admission_no', 'students.firstname',
                'students.lastname', 'attendence_type.type')->get();

        $types = DB::table('attendence_type')->select('id', 'type')->get();

        return $this->successResponse(['report' => $rows, 'attendence_types' => $types]);
    }

    public function subjectAttendance(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        $v = $request->validate([
            'subject_timetable_id' => 'required|integer', 'date' => 'required|date',
        ]);

        // نطاق المعلم: الحصة المسندة له في الجلسة الحالية
        $slot = DB::table('subject_timetable')->where('id', $v['subject_timetable_id'])
            ->when($ctx->sessionId, fn ($q) => $q->where('session_id', $ctx->sessionId))->first();
        if (! $slot || (int) $slot->staff_id !== (int) $ctx->staff->id) {
            return $this->errorResponse('Timetable slot is not in your scope', null, 403);
        }

        $students = DB::table('student_session')
            ->join('students', 'students.id', '=', 'student_session.student_id')
            ->leftJoin('student_subject_attendances', function ($j) use ($v) {
                $j->on('student_subject_attendances.student_session_id', '=', 'student_session.id')
                    ->where('student_subject_attendances.subject_timetable_id', $v['subject_timetable_id'])
                    ->whereDate('student_subject_attendances.date', $v['date']);
            })
            ->leftJoin('attendence_type', 'attendence_type.id', '=', 'student_subject_attendances.attendence_type_id')
            ->where('students.is_active', 'yes')
            ->where('student_session.class_id', $slot->class_id)
            ->where('student_session.section_id', $slot->section_id)
            ->when($ctx->sessionId, fn ($q) => $q->where('student_session.session_id', $ctx->sessionId))
            ->select('students.id', 'students.admission_no', 'students.firstname', 'students.lastname',
                'student_session.id as student_session_id',
                'student_subject_attendances.id as attendence_id',
                'student_subject_attendances.attendence_type_id',
                'attendence_type.type as attendence_type')
            ->orderBy('students.id')->get();

        return $this->successResponse(['students' => $students, 'slot' => $slot]);
    }

    public function storeSubjectAttendance(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        $v = $request->validate([
            'subject_timetable_id' => 'required|integer', 'date' => 'required|date',
            'records' => 'required|array|min:1',
            'records.*.student_session_id' => 'required|integer',
            'records.*.attendence_type_id' => 'required|integer|exists:attendence_type,id',
            'records.*.remark' => 'nullable|string|max:500',
        ]);

        $slot = DB::table('subject_timetable')->where('id', $v['subject_timetable_id'])
            ->when($ctx->sessionId, fn ($q) => $q->where('session_id', $ctx->sessionId))->first();
        if (! $slot || (int) $slot->staff_id !== (int) $ctx->staff->id) {
            return $this->errorResponse('Timetable slot is not in your scope', null, 403);
        }

        $validIds = DB::table('student_session')
            ->where('class_id', $slot->class_id)->where('section_id', $slot->section_id)
            ->when($ctx->sessionId, fn ($q) => $q->where('session_id', $ctx->sessionId))
            ->pluck('id')->map(fn ($x) => (int) $x)->all();

        $now = now(); $inserted = 0; $updated = 0;
        DB::transaction(function () use ($v, $validIds, $now, &$inserted, &$updated) {
            foreach ($v['records'] as $rec) {
                $ssid = (int) $rec['student_session_id'];
                if (! in_array($ssid, $validIds, true)) continue;
                $existing = DB::table('student_subject_attendances')
                    ->where('student_session_id', $ssid)
                    ->where('subject_timetable_id', $v['subject_timetable_id'])
                    ->whereDate('date', $v['date'])->first();
                // CI parity (Subjectattendence::index → Studentsubjectattendence_model::add):
                // student_subject_attendances has no created_at/updated_at handling in CI —
                // the table has no updated_at column (created_at fills via DB default).
                $row = ['student_session_id' => $ssid, 'subject_timetable_id' => $v['subject_timetable_id'],
                    'attendence_type_id' => (int) $rec['attendence_type_id'],
                    'remark' => $rec['remark'] ?? null, 'date' => $v['date']];
                if ($existing) {
                    DB::table('student_subject_attendances')->where('id', $existing->id)->update($row);
                    $updated++;
                } else {
                    DB::table('student_subject_attendances')->insert($row);
                    $inserted++;
                }
            }
        });

        return $this->successResponse(['inserted' => $inserted, 'updated' => $updated], 'Subject attendance saved successfully');
    }

    /** حضور شهري لطالب — parity مع CI Stuattendence::monthAttendance */
    public function monthAttendance(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        $v = $request->validate([
            'student_id' => 'required|integer|exists:students,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:2100',
        ]);

        $ss = DB::table('student_session')
            ->where('student_id', $v['student_id'])
            ->when($ctx->sessionId, fn ($q) => $q->where('session_id', $ctx->sessionId))
            ->first();
        if (! $ss) {
            return $this->errorResponse('Student not found in current session', null, 404);
        }
        $csId = (int) DB::table('class_sections')
            ->where('class_id', $ss->class_id)->where('section_id', $ss->section_id)->value('id');
        if (! $csId || ! TeacherScope::inScope((int) $ctx->staff->id, $ctx->sessionId, $csId)) {
            return $this->errorResponse('Student is not in your scope', null, 403);
        }

        $rows = DB::table('student_attendences')
            ->leftJoin('attendence_type', 'attendence_type.id', '=', 'student_attendences.attendence_type_id')
            ->where('student_attendences.student_session_id', $ss->id)
            ->whereMonth('student_attendences.date', $v['month'])
            ->whereYear('student_attendences.date', $v['year'])
            ->select('student_attendences.date', 'student_attendences.attendence_type_id',
                'student_attendences.remark', 'attendence_type.type as attendence_type')
            ->orderBy('student_attendences.date')->get();

        $summary = $rows->groupBy('attendence_type')->map(fn ($g) => count($g));

        return $this->successResponse(['days' => $rows, 'summary' => $summary]);
    }
}
