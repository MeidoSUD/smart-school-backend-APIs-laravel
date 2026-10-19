<?php

namespace Modules\Staff\Http\Controllers\Api\Teacher;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * إجازتي الوظيفية للمعلم — parity مع CI:
 * - Staff::leaverequest/staffLeaveSummary (تقديم + رصيد)
 * - Leaverequest_model::staff_leave_request/countLeavesData/allotedLeaveType
 * ملاحظة: هذا غير Approve_leave (اعتماد إجازات الطلاب) الموجود في TeacherLeaveController.
 */
class TeacherMyLeaveController extends TeacherController
{
    public function __construct()
    {
        $this->setControllerName('TeacherMyLeaveController');
    }

    public function leaveTypes(Request $request): JsonResponse
    {
        $this->context($request);

        $types = DB::table('leave_types')->where('is_active', 'yes')->select('id', 'type')->get();

        return $this->successResponse(['leave_types' => $types]);
    }

    public function myLeaves(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        $v = $request->validate(['status' => 'nullable|string|max:20']);

        $query = DB::table('staff_leave_request')
            ->leftJoin('leave_types', 'leave_types.id', '=', 'staff_leave_request.leave_type_id')
            ->where('staff_leave_request.staff_id', $ctx->staff->id)
            ->select('staff_leave_request.*', 'leave_types.type as leave_type')
            ->orderByDesc('staff_leave_request.id');

        if (! empty($v['status'])) {
            $query->where('staff_leave_request.status', $v['status']);
        }

        return $this->successResponse(['leaves' => $query->paginate(25)]);
    }

    public function balance(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        // parity مع Leaverequest_model::allotedLeaveType + countLeavesData:
        // المخصص من staff_leave_details أو leave_types، والمستخدم = approved فقط
        $allotted = DB::table('staff_leave_details')
            ->where('staff_id', $ctx->staff->id)->get(['leave_type_id', 'alloted_leave'])
            ->keyBy('leave_type_id');

        $types = DB::table('leave_types')->where('is_active', 'yes')->select('id', 'type')->get();

        $used = DB::table('staff_leave_request')
            ->where('staff_id', $ctx->staff->id)
            ->whereIn('status', ['approve', 'approved'])
            ->select('leave_type_id', DB::raw('COALESCE(SUM(leave_days),0) as used_days'))
            ->groupBy('leave_type_id')->pluck('used_days', 'leave_type_id');

        $balance = $types->map(function ($t) use ($allotted, $used) {
            $allow = isset($allotted[$t->id]) ? (int) $allotted[$t->id]->alloted_leave : 0;

            return [
                'leave_type_id' => $t->id,
                'leave_type' => $t->type,
                'allotted' => $allow,
                'used' => (int) ($used[$t->id] ?? 0),
                'remaining' => $allow - (int) ($used[$t->id] ?? 0),
            ];
        });

        return $this->successResponse(['balance' => $balance]);
    }

    public function apply(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        $v = $request->validate([
            'leave_type_id' => 'required|integer|exists:leave_types,id',
            'leave_from' => 'required|date',
            'leave_to' => 'required|date|after_or_equal:leave_from',
            'employee_remark' => 'required|string|max:1000',
            'document_file' => 'nullable|string|max:255',
        ]);

        $from = new \DateTime($v['leave_from']);
        $to = new \DateTime($v['leave_to']);
        $days = (int) $from->diff($to)->format('%a') + 1;

        // منع التقديم المكرر لنفس الفترة pending
        $overlap = DB::table('staff_leave_request')
            ->where('staff_id', $ctx->staff->id)
            ->where('status', 'pending')
            ->where('leave_from', '<=', $v['leave_to'])
            ->where('leave_to', '>=', $v['leave_from'])
            ->exists();
        if ($overlap) {
            return $this->errorResponse('You already have a pending leave for this period', null, 422);
        }

        $id = DB::table('staff_leave_request')->insertGetId([
            'staff_id' => $ctx->staff->id,
            'leave_type_id' => $v['leave_type_id'],
            'leave_from' => $v['leave_from'],
            'leave_to' => $v['leave_to'],
            'leave_days' => $days,
            'employee_remark' => $v['employee_remark'],
            // CI parity (Leaverequest::add_staff_leave): '' defaults, NOT NULL cols.
            'admin_remark' => '',
            'document_file' => $v['document_file'] ?? '',
            'status' => 'pending',
            'date' => date('Y-m-d'),
            'applied_by' => $ctx->staff->id,
            'created_at' => now(),
        ]);

        return $this->successResponse(['id' => $id], 'Leave applied successfully');
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        $ctx = $this->context($request);

        $leave = DB::table('staff_leave_request')
            ->where('id', $id)->where('staff_id', $ctx->staff->id)->first();
        if (! $leave) {
            return $this->errorResponse('Leave not found', null, 404);
        }
        if ($leave->status !== 'pending') {
            return $this->errorResponse('Only pending leaves can be cancelled', null, 422);
        }

        DB::table('staff_leave_request')->where('id', $id)->delete();

        return $this->successResponse(null, 'Leave cancelled successfully');
    }
}
