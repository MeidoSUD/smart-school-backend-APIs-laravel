<?php

namespace Modules\Staff\Http\Controllers\Api\Teacher;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Staff\Services\TeacherScope;

/**
 * إضافات الواجبات — parity مع CI Homework الكامل:
 * - closedHomeworkList (closehomeworklist): المنتهية submit_date < اليوم
 * - homeworkReport/countPercentage (evaluation_report/getreport/count_percentage)
 * - dailyAssignment CRUD + details + remark (dailyassignment + assignmentdetails + submitassignmentremark)
 */
class TeacherHomeworkExtraController extends TeacherController
{
    public function __construct()
    {
        $this->setControllerName('TeacherHomeworkExtraController');
    }

    public function closedList(Request $request): JsonResponse
    {
        $ctx = $this->context($request);
        $classes = TeacherScope::myClasses((int) $ctx->staff->id, $ctx->sessionId);
        $pairs = collect($classes)->map(fn ($c) => [$c['class_id'], $c['section_id']])->all();

        $query = DB::table('homework')
            ->leftJoin('subject_group_subjects as sgs', 'sgs.id', '=', 'homework.subject_group_subject_id')
            ->leftJoin('subjects', 'subjects.id', '=', 'sgs.subject_id')
            ->whereDate('homework.submit_date', '<', date('Y-m-d'))
            ->when($ctx->sessionId, fn ($q) => $q->where('homework.session_id', $ctx->sessionId))
            ->select('homework.*', 'subjects.name as subject_name')
            ->orderByDesc('homework.submit_date');

        if (! empty($pairs)) {
            $query->where(function ($q) use ($pairs) {
                foreach ($pairs as [$cid, $sid]) {
                    $q->orWhere(fn ($qq) => $qq->where('homework.class_id', $cid)->where('homework.section_id', $sid));
                }
            });
        } else {
            $query->whereRaw('1=0');
        }

        return $this->successResponse(['homework' => $query->paginate(25)]);
    }

    public function evaluationReport(Request $request): JsonResponse
    {
        $ctx = $this->context($request);
        $v = $request->validate(['homework_id' => 'required|integer|exists:homework,id']);

        $hw = DB::table('homework')->where('id', $v['homework_id'])->first();
        $csId = (int) DB::table('class_sections')
            ->where('class_id', $hw->class_id)->where('section_id', $hw->section_id)->value('id');
        if (! $csId || ! TeacherScope::inScope((int) $ctx->staff->id, $ctx->sessionId, $csId)) {
            return $this->errorResponse('Homework is not in your scope', null, 403);
        }

        $total = (int) DB::table('student_session')
            ->join('students', 'students.id', '=', 'student_session.student_id')
            ->where('students.is_active', 'yes')
            ->where('student_session.class_id', $hw->class_id)
            ->where('student_session.section_id', $hw->section_id)
            ->when($ctx->sessionId, fn ($q) => $q->where('student_session.session_id', $ctx->sessionId))
            ->count();
        $evaluated = (int) DB::table('homework_evaluation')->where('homework_id', $hw->id)->count();
        $pending = $total - $evaluated;

        return $this->successResponse([
            'homework_id' => $hw->id,
            'total_students' => $total,
            'evaluated' => $evaluated,
            'pending' => max($pending, 0),
            'complete_percent' => $total ? round($evaluated * 100 / $total, 2) : 0,
        ]);
    }

    public function dailyAssignments(Request $request): JsonResponse
    {
        $ctx = $this->context($request);
        $classes = TeacherScope::myClasses((int) $ctx->staff->id, $ctx->sessionId);
        $pairs = collect($classes)->map(fn ($c) => [$c['class_id'], $c['section_id']])->all();

        $csIds = collect($classes)->pluck('class_section_id')->all();
        $subjectIds = DB::table('teacher_subjects')->where('teacher_id', $ctx->staff->id)
            ->when(! empty($csIds), fn ($q) => $q->whereIn('class_section_id', $csIds))
            ->distinct()->pluck('subject_id')->map(fn ($x) => (int) $x)->all();
        $sgsIds = $subjectIds !== []
            ? DB::table('subject_group_subjects')->whereIn('subject_id', $subjectIds)->pluck('id')->map(fn ($x) => (int) $x)->all()
            : [];

        $query = DB::table('daily_assignment')
            ->leftJoin('subject_group_subjects as sgs', 'sgs.id', '=', 'daily_assignment.subject_group_subject_id')
            ->leftJoin('subjects', 'subjects.id', '=', 'sgs.subject_id')
            ->select('daily_assignment.*', 'subjects.name as subject_name')
            ->orderByDesc('daily_assignment.date');
        if (! empty($sgsIds)) {
            $query->whereIn('daily_assignment.subject_group_subject_id', $sgsIds);
        } else {
            $query->whereRaw('1=0');
        }

        return $this->successResponse(['assignments' => $query->paginate(25)]);
    }

    public function storeDailyAssignment(Request $request): JsonResponse
    {
        $ctx = $this->context($request);
        $v = $request->validate([
            'subject_group_subject_id' => 'required|integer|exists:subject_group_subjects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'attachment' => 'nullable|string|max:255',
        ]);

        $studentSessionId = (int) $request->input('student_session_id', 0);
        if ($studentSessionId <= 0) {
            $studentSessionId = (int) (DB::table('student_session')
                ->where('session_id', $ctx->sessionId)
                ->value('id') ?? 1);
        }

        $id = DB::table('daily_assignment')->insertGetId([
            'student_session_id' => $studentSessionId,
            'subject_group_subject_id' => $v['subject_group_subject_id'],
            'title' => $v['title'],
            'description' => $v['description'] ?? null,
            'attachment' => $v['attachment'] ?? null,
            'date' => $v['date'],
            'evaluated_by' => $ctx->staff->id,
            'remark' => '',
            'created_at' => now(),
        ]);

        return $this->successResponse(['id' => $id], 'Daily assignment created successfully');
    }

    public function dailyAssignmentDetails(Request $request, int $id): JsonResponse
    {
        $this->context($request);
        $row = DB::table('daily_assignment')->where('id', $id)->first();
        if (! $row) {
            return $this->errorResponse('Assignment not found', null, 404);
        }

        return $this->successResponse(['assignment' => $row]);
    }

    public function submitDailyRemark(Request $request): JsonResponse
    {
        $ctx = $this->context($request);
        $v = $request->validate([
            'id' => 'required|integer|exists:daily_assignment,id',
            'remark' => 'required|string|max:1000',
        ]);

        DB::table('daily_assignment')->where('id', $v['id'])->update([
            'remark' => $v['remark'],
            'evaluation_date' => date('Y-m-d'),
            'evaluated_by' => $ctx->staff->id,
        ]);

        return $this->successResponse(null, 'Remark saved successfully');
    }
}
