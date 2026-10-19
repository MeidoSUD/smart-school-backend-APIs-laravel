<?php

declare(strict_types=1);

namespace Modules\Staff\Http\Controllers\Api\Teacher;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Staff\Services\TeacherRbac;

/**
 * أسباب التعطيل — عمليات الكتابة للمعلم، parity مع CI admin/Disable_reason:
 * - add    (disable_reason:can_add)    → Disable_reason_model::add (insert)
 * - edit   (disable_reason:can_edit)   → Disable_reason_model::add (update بالـ id)
 * - delete (disable_reason:can_delete) → Disable_reason_model::remove
 * الصلاحيات تُفحص عبر TeacherRbac (مطابق لـ CI Rbac::hasPrivilege).
 * القراءة موجودة في TeacherAcademicController.
 */
class TeacherDisableReasonController extends TeacherController
{
    public function __construct()
    {
        $this->setControllerName('TeacherDisableReasonController');
    }

    public function store(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        if (! TeacherRbac::hasPrivilege((int) $ctx->staff->id, 'disable_reason', 'can_add')) {
            return $this->errorResponse('Forbidden: missing disable_reason can_add permission', null, 403);
        }

        $v = $request->validate(['name' => 'required|string|max:255']);

        $id = DB::table('disable_reason')->insertGetId([
            'reason' => $v['name'],
            'created_at' => now(),
        ]);

        return $this->successResponse(['id' => $id], 'Disable reason created successfully');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $ctx = $this->context($request);

        if (! TeacherRbac::hasPrivilege((int) $ctx->staff->id, 'disable_reason', 'can_edit')) {
            return $this->errorResponse('Forbidden: missing disable_reason can_edit permission', null, 403);
        }

        $v = $request->validate(['name' => 'required|string|max:255']);

        $updated = DB::table('disable_reason')->where('id', $id)->update(['reason' => $v['name']]);

        if ($updated === 0 && ! DB::table('disable_reason')->where('id', $id)->exists()) {
            return $this->errorResponse('Disable reason not found', null, 404);
        }

        return $this->successResponse(['id' => $id], 'Disable reason updated successfully');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $ctx = $this->context($request);

        if (! TeacherRbac::hasPrivilege((int) $ctx->staff->id, 'disable_reason', 'can_delete')) {
            return $this->errorResponse('Forbidden: missing disable_reason can_delete permission', null, 403);
        }

        $deleted = DB::table('disable_reason')->where('id', $id)->delete();

        if ($deleted === 0) {
            return $this->errorResponse('Disable reason not found', null, 404);
        }

        return $this->successResponse(null, 'Disable reason deleted successfully');
    }
}
