<?php

namespace App\Services;

use Modules\Core\Entities\PermissionCategory;
use Modules\Core\Entities\Role;
use Modules\Core\Entities\SidebarMenu;
use Modules\Core\Entities\SidebarSubMenu;
use Modules\Core\Entities\User;

class MenuBuilderService
{
    public function getMenuForUser(User $user): array
    {
        if ($user->isSuperAdmin()) {
            return $this->getAllMenus();
        }

        $role = $user->roleModel;
        if (!$role) {
            return [];
        }

        $permissionIds = $role->permissionCategories()
            ->where('roles_permissions.is_active', 1)
            ->pluck('permission_category.id')
            ->toArray();

        $groupIds = PermissionCategory::whereIn('id', $permissionIds)
            ->pluck('permission_group_id')
            ->unique()
            ->toArray();

        $menus = SidebarMenu::whereIn('permission_group_id', $groupIds)
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->get();

        return $menus->map(function ($menu) use ($permissionIds) {
            $subMenus = SidebarSubMenu::where('sidebar_menu_id', $menu->id)
                ->where('is_active', 1)
                ->orderBy('sort_order')
                ->get()
                ->filter(function ($sub) use ($permissionIds) {
                    return in_array($sub->permission_group_id, $permissionIds) || $sub->permission_group_id == 0;
                })
                ->values();

            return [
                'id' => $menu->id,
                'name' => $menu->menu_name,
                'icon' => $menu->icon,
                'url' => $menu->menu_url,
                'module' => $menu->module_name,
                'sub_menus' => $subMenus->map(fn($sub) => [
                    'id' => $sub->id,
                    'name' => $sub->menu_name,
                    'url' => $sub->menu_url,
                ])->toArray(),
            ];
        })->toArray();
    }

    public function getAllMenus(): array
    {
        $menus = SidebarMenu::where('is_active', 1)
            ->orderBy('sort_order')
            ->get();

        return $menus->map(function ($menu) {
            $subMenus = SidebarSubMenu::where('sidebar_menu_id', $menu->id)
                ->where('is_active', 1)
                ->orderBy('sort_order')
                ->get();

            return [
                'id' => $menu->id,
                'name' => $menu->menu_name,
                'icon' => $menu->icon,
                'url' => $menu->menu_url,
                'module' => $menu->module_name,
                'sub_menus' => $subMenus->map(fn($sub) => [
                    'id' => $sub->id,
                    'name' => $sub->menu_name,
                    'url' => $sub->menu_url,
                ])->toArray(),
            ];
        })->toArray();
    }
}
