<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::latest()->paginate(15);

        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        return view('roles.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'is_active' => 'nullable|integer',
            'is_superadmin' => 'nullable|integer',
            'is_staff' => 'nullable|integer',
            'is_student' => 'nullable|integer',
            'is_parent' => 'nullable|integer',
            'is_admin' => 'nullable|integer',
        ]);

        Role::create($validated);

        return redirect()->route('admin.roles.index')->with('success', 'Role created successfully.');
    }

    public function edit(Role $role)
    {
        return view('roles.edit', compact('role'));
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'is_active' => 'nullable|integer',
            'is_superadmin' => 'nullable|integer',
            'is_staff' => 'nullable|integer',
            'is_student' => 'nullable|integer',
            'is_parent' => 'nullable|integer',
            'is_admin' => 'nullable|integer',
        ]);

        $role->update($validated);

        return redirect()->route('admin.roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        $role->delete();

        return redirect()->route('admin.roles.index')->with('success', 'Role deleted successfully.');
    }
}
