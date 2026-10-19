<?php

namespace Modules\Staff\Http\Controllers\Api\Teacher;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Staff\Services\TeacherScope;

/**
 * إجازات طلاب فصول المعلم — parity مع CI admin/Approve_leave:
 * - index: إجازات طلاب نطاق المعلم
 * - status: اعتماد/رفض مع بوابة canApproveLeave (الطالب في فصول المعلم) + approve_by
 */
class TeacherLeaveController extends TeacherController
{
    public function __construct()
    {
        $this->setControllerName('TeacherLeaveController');
    }

    /** CI parity (admin/Approve_leave): status is INT — 0=pending, 1=approve, 2=disapprove. */
    private const STATUS_MAP = [
        'pending' => 0,
        'approve' => 1,
        'approved' => 1,
        'disapprove' => 2,
        'disapproved' => 2,
    ];
    private function leaveInScope(int $staffId, int $sessionId, int $leaveId): bool
    {
        $leave = DB::table('student_applyleave')->where('id', $leaveId)->first();
        if (! $leave) return false;
        $ss = DB::table('student_session')->where('id', $leave->student_session_id)->first();
        if (! $ss) return false;
        if ((int) $ss->session_id !== $sessionId) return false;
        $csId = (int) DB::table('class_sections')
            ->where('class_id', $ss->class_id)->where('section_id', $ss->section_id)->value('id');

        return $csId && TeacherScope::inScope($staffId, $sessionId, $csId);
    }

    public function index(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        $v = $request->validate(['status' => 'nullable|string|max:20']);

        $classes = TeacherScope::myClasses((int) $ctx->staff->id, $ctx->sessionId);
        $pairs = collect($classes)->map(fn ($c) => [$c['class_id'], $c['section_id']])->all();

        $query = DB::table('student_applyleave')
            ->join('student_session', 'student_session.id', '=', 'student_applyleave.student_session_id')
            ->join('students', 'students.id', '=', 'student_session.student_id')
            ->leftJoin('classes', 'classes.id', '=', 'student_session.class_id')
            ->leftJoin('sections', 'sections.id', '=', 'student_session.section_id')
            ->when($ctx->sessionId, fn ($q) => $q->where('student_session.session_id', $ctx->sessionId))
            ->select('student_applyleave.*', 'students.admission_no', 'students.firstname',
                'students.lastname', 'classes.class as class_name', 'sections.section as section_name')
            ->orderByDesc('student_applyleave.apply_date');

        if (! empty($pairs)) {
            $query->where(function ($q) use ($pairs) {
                foreach ($pairs as [$cid, $sid]) {
                    $q->orWhere(fn ($qq) => $qq->where('student_session.class_id', $cid)->where('student_session.section_id', $sid));
                }
            });
        } else {
            $query->whereRaw('1=0');
        }
        if (! empty($v['status'])) $query->where('student_applyleave.status', self::STATUS_MAP[strtolower($v['status'])] ?? $v['status']);

        return $this->successResponse(['leaves' => $query->paginate(25)]);
    }

    public function setStatus(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        $v = $request->validate([
            'id' => 'required|integer|exists:student_applyleave,id',
            'status' => 'required|string|in:approve,approved,disapprove,disapproved,pending',
        ]);

        if (! $this->leaveInScope((int) $ctx->staff->id, $ctx->sessionId, (int) $v['id'])) {
            return $this->errorResponse('Leave is not in your scope', null, 403);
        }

        // CI parity: Approve_leave::status stores the INT status directly;
        // approve_by = staff ID only when status == 1, otherwise "nobody".
        // (CI writes literal 0, but the approve_by→staff FK — present in the
        // CI schema itself — rejects 0; NULL carries the same "no approver"
        // semantics and keeps the LEFT-join reads identical.)
        $statusInt = self::STATUS_MAP[strtolower($v['status'])];
        DB::table('student_applyleave')->where('id', $v['id'])->update([
            'status' => $statusInt,
            'approve_by' => $statusInt === 1 ? $ctx->staff->id : null,
        ]);

        return $this->successResponse(null, 'Leave status updated successfully');
    }
}
