<?php
namespace App\Http\Controllers\Api;

use App\Models\Role;
use App\Http\Resources\RoleResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends \App\Http\Controllers\Api\Controller
{
    public function __construct()
    {
        $this->setControllerName('RoleController');
    }

    public function index(): JsonResponse
    {
        $roles = Role::with('permissionCategories')->get();
        return $this->successResponse(RoleResource::collection($roles));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:roles,name',
        ]);

        $role = Role::create($request->only('name', 'is_active', 'is_superadmin', 'is_staff', 'is_student', 'is_parent', 'is_admin'));

        return $this->successResponse(new RoleResource($role->load('permissionCategories')), 'Role created', 201);
    }

    public function show(Role $role): JsonResponse
    {
        $role->load('permissionCategories');
        return $this->successResponse(new RoleResource($role));
    }

    public function update(Request $request, Role $role): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:roles,name,' . $role->id,
        ]);

        $role->update($request->only('name', 'is_active', 'is_superadmin', 'is_staff', 'is_student', 'is_parent', 'is_admin'));

        return $this->successResponse(new RoleResource($role->load('permissionCategories')), 'Role updated');
    }

    public function destroy(Role $role): JsonResponse
    {
        $role->delete();
        return $this->successResponse(null, 'Role deleted');
    }
}
