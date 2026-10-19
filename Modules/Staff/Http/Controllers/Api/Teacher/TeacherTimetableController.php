<?php

namespace Modules\Staff\Http\Controllers\Api\Teacher;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Staff\Services\TeacherScope;

/**
 * جدول المعلم — parity مع:
 * - CI admin/timetable/mytimetable (حصص staff_id في الجلسة الحالية)
 * - CI admin/timetable/classreport (جدول فصل ضمن نطاقه)
 */
class TeacherTimetableController extends TeacherController
{
    public function __construct()
    {
        $this->setControllerName('TeacherTimetableController');
    }

    public function myTimetable(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        $rows = DB::table('subject_timetable')
            ->leftJoin('subject_group_subjects as sgs', 'sgs.id', '=', 'subject_timetable.subject_group_subject_id')
            ->leftJoin('subjects', 'subjects.id', '=', 'sgs.subject_id')
            ->leftJoin('classes', 'classes.id', '=', 'subject_timetable.class_id')
            ->leftJoin('sections', 'sections.id', '=', 'subject_timetable.section_id')
            ->where('subject_timetable.staff_id', $ctx->staff->id)
            ->when($ctx->sessionId, fn ($q) => $q->where('subject_timetable.session_id', $ctx->sessionId))
            ->select('subject_timetable.*', 'subjects.name as subject_name',
                'classes.class as class_name', 'sections.section as section_name')
            ->orderBy('subject_timetable.day')->orderBy('subject_timetable.start_time')->get();

        return $this->successResponse(['timetable' => $rows]);
    }

    public function classReport(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        $v = $request->validate([
            'class_id' => 'required|integer', 'section_id' => 'required|integer',
            'day' => 'nullable|string|max:15',
        ]);

        $csId = (int) DB::table('class_sections')
            ->where('class_id', $v['class_id'])->where('section_id', $v['section_id'])->value('id');
        if (! $csId || ! TeacherScope::inScope((int) $ctx->staff->id, $ctx->sessionId, $csId)) {
            return $this->errorResponse('Class section is not in your scope', null, 403);
        }

        $rows = DB::table('subject_timetable')
            ->leftJoin('subject_group_subjects as sgs', 'sgs.id', '=', 'subject_timetable.subject_group_subject_id')
            ->leftJoin('subjects', 'subjects.id', '=', 'sgs.subject_id')
            ->leftJoin('staff', 'staff.id', '=', 'subject_timetable.staff_id')
            ->where('subject_timetable.class_id', $v['class_id'])
            ->where('subject_timetable.section_id', $v['section_id'])
            ->when($ctx->sessionId, fn ($q) => $q->where('subject_timetable.session_id', $ctx->sessionId))
            ->when(! empty($v['day']), fn ($q) => $q->where('subject_timetable.day', $v['day']))
            ->select('subject_timetable.*', 'subjects.name as subject_name',
                'staff.name as teacher_name', 'staff.surname as teacher_surname')
            ->orderBy('subject_timetable.day')->orderBy('subject_timetable.start_time')->get();

        return $this->successResponse(['timetable' => $rows]);
    }
}
