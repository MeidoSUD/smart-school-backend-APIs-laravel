<?php

declare(strict_types=1);

namespace Modules\Staff\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * فحص صلاحيات RBAC الدقيقة — parity مع CI Rbac::hasPrivilege:
 * roles_permissions(role_id, perm_cat short_code) → can_view/add/edit/delete.
 * يُستخدم كطبقة إضافية فوق TeacherScope (النطاق) — لا بدلاً منه.
 * Super Admin (role 7 / name Super Admin) يتجاوز الكل مثل CI.
 */
final class TeacherRbac
{
    public static function hasPrivilege(int $staffId, string $category, string $permission): bool
    {
        $roles = TeacherScope::staffRoles($staffId);
        if ($roles === []) {
            return false;
        }
        foreach ($roles as $r) {
            if ((int) $r->role_id === 7 || strtolower((string) $r->name) === 'super admin') {
                return true;
            }
        }
        if (! Schema::hasTable('roles_permissions') || ! Schema::hasTable('permission_category')) {
            return true;
        }
        $roleId = (int) $roles[0]->role_id;
        $row = DB::table('roles_permissions')
            ->join('permission_category', 'permission_category.id', '=', 'roles_permissions.perm_cat_id')
            ->where('roles_permissions.role_id', $roleId)
            ->where('permission_category.short_code', trim($category))
            ->first([$permission.' as v']);
        if (! $row) {
            return false;
        }

        return (int) ($row->v ?? 0) === 1;
    }
}
