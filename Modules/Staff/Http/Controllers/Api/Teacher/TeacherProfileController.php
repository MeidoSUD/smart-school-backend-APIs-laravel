<?php

namespace Modules\Staff\Http\Controllers\Api\Teacher;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * بروفايل المعلم — parity مع CI admin/Staff::profile + changepass + documents:
 * - profile: بيانات staff + designation/department/roles + رصيد الإجازات + إحصاء الحضور
 * - update: حقول مسموحة فقط (تواصل/عنوان/سوشيال) — لا راتب/قسم/تعيين
 * - changePassword: users.password + staff.password معاً (parity مع CI)
 */
class TeacherProfileController extends TeacherController
{
    public function __construct()
    {
        $this->setControllerName('TeacherProfileController');
    }

    public function profile(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        $staff = DB::table('staff')
            ->leftJoin('staff_designation as d', 'd.id', '=', 'staff.designation')
            ->leftJoin('department as dep', 'dep.id', '=', 'staff.department')
            ->where('staff.id', $ctx->staff->id)
            ->select('staff.*', 'd.designation as designation_name', 'dep.department_name as department_name')
            ->first();

        $leaves = DB::table('staff_leave_request')
            ->leftJoin('leave_types', 'leave_types.id', '=', 'staff_leave_request.leave_type_id')
            ->where('staff_leave_request.staff_id', $ctx->staff->id)
            ->select('staff_leave_request.*', 'leave_types.type as leave_type')
            ->orderByDesc('staff_leave_request.id')->limit(20)->get();

        $attendanceSummary = DB::table('staff_attendance')
            ->leftJoin('staff_attendance_type as t', 't.id', '=', 'staff_attendance.staff_attendance_type_id')
            ->select('t.type as type', DB::raw('COUNT(*) as days'))
            ->where('staff_attendance.staff_id', $ctx->staff->id)
            ->groupBy('t.type')->get();

        return $this->successResponse([
            'staff' => $staff,
            'roles' => $ctx->roles,
            'session_id' => $ctx->sessionId,
            'recent_leaves' => $leaves,
            'attendance_summary' => $attendanceSummary,
            'user' => ['id' => $ctx->user->id, 'username' => $ctx->user->username, 'role' => $ctx->user->role],
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        $v = $request->validate([
            'contact_no' => 'sometimes|string|max:30',
            'emergency_contact_no' => 'sometimes|string|max:30',
            'email' => 'sometimes|email|max:100',
            'local_address' => 'sometimes|string|max:500',
            'permanent_address' => 'sometimes|string|max:500',
            'facebook' => 'sometimes|nullable|string|max:255',
            'twitter' => 'sometimes|nullable|string|max:255',
            'linkedin' => 'sometimes|nullable|string|max:255',
            'instagram' => 'sometimes|nullable|string|max:255',
        ]);

        if ($v !== []) {
            DB::table('staff')->where('id', $ctx->staff->id)->update($v);
        }

        return $this->successResponse(null, 'Profile updated successfully');
    }

    public function changePassword(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        $v = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|max:100',
        ]);

        $user = $ctx->user;
        $staffRow = DB::table('staff')->where('id', $ctx->staff->id)->first();

        $userOk = $user->password !== null && (hash_equals((string) $user->password, $v['current_password']) || Hash::check($v['current_password'], (string) $user->password));
        $staffOk = $staffRow && $staffRow->password && Hash::check($v['current_password'], $staffRow->password);
        if (! $userOk && ! $staffOk) {
            return $this->errorResponse('Current password is incorrect', null, 422);
        }

        DB::transaction(function () use ($user, $ctx, $v) {
            $user->password = $v['new_password'];
            $user->save();
            DB::table('staff')->where('id', $ctx->staff->id)->update([
                'password' => Hash::make($v['new_password']),
            ]);
            DB::table('users')->where('id', $user->id)->update([
                'password' => $v['new_password'],
            ]);
        });

        return $this->successResponse(null, 'Password changed successfully');
    }
}
