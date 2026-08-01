<?php

namespace App\Http\Controllers\Api;

use App\Models\Staff;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffController extends \App\Http\Controllers\Api\Controller
{
    public function __construct()
    {
        $this->setControllerName('StaffController');
    }

    public function index(Request $request): JsonResponse
    {
        $query = Staff::with(['designation', 'department']);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('staff_name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%")
                    ->orWhere('phone', 'like', "%{$request->search}%");
            });
        }

        if ($request->department_id) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->designation_id) {
            $query->where('staff_designation_id', $request->designation_id);
        }

        $staff = $query->get();

        return $this->successResponse($staff);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'staff_name' => 'required|string|max:255',
            'email' => 'required|email|unique:staff,email',
            'phone' => 'required|string|max:255',
            'role_id' => 'required',
            'staff_designation_id' => 'required',
            'department_id' => 'required',
        ]);

        $staff = Staff::create($validated);

        return $this->successResponse($staff, 'Staff created successfully');
    }

    public function show($id): JsonResponse
    {
        $staff = Staff::with(['designation', 'department', 'user'])->find($id);

        if (!$staff) {
            return $this->errorResponse('Staff not found', null, 404);
        }

        return $this->successResponse($staff);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $staff = Staff::find($id);

        if (!$staff) {
            return $this->errorResponse('Staff not found', null, 404);
        }

        $validated = $request->validate([
            'staff_name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:staff,email,' . $id,
            'phone' => 'sometimes|required|string|max:255',
            'role_id' => 'sometimes|required',
            'staff_designation_id' => 'sometimes|required',
            'department_id' => 'sometimes|required',
        ]);

        $staff->update($validated);

        return $this->successResponse($staff, 'Staff updated successfully');
    }

    public function destroy($id): JsonResponse
    {
        $staff = Staff::find($id);

        if (!$staff) {
            return $this->errorResponse('Staff not found', null, 404);
        }

        $staff->delete();

        return $this->successResponse(null, 'Staff deleted successfully');
    }
}
