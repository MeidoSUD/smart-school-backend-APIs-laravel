<?php

namespace App\Http\Controllers\Api;

use App\Models\StaffDesignation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffDesignationController extends \App\Http\Controllers\Api\Controller
{
    public function __construct()
    {
        $this->setControllerName('StaffDesignationController');
    }

    public function index(Request $request): JsonResponse
    {
        $designations = StaffDesignation::all();

        return $this->successResponse($designations);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'staff_designation_name' => 'required|string|max:255',
        ]);

        $designation = StaffDesignation::create($validated);

        return $this->successResponse($designation, 'Designation created successfully');
    }

    public function show($id): JsonResponse
    {
        $designation = StaffDesignation::find($id);

        if (!$designation) {
            return $this->errorResponse('Designation not found', null, 404);
        }

        return $this->successResponse($designation);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $designation = StaffDesignation::find($id);

        if (!$designation) {
            return $this->errorResponse('Designation not found', null, 404);
        }

        $validated = $request->validate([
            'staff_designation_name' => 'sometimes|required|string|max:255',
        ]);

        $designation->update($validated);

        return $this->successResponse($designation, 'Designation updated successfully');
    }

    public function destroy($id): JsonResponse
    {
        $designation = StaffDesignation::find($id);

        if (!$designation) {
            return $this->errorResponse('Designation not found', null, 404);
        }

        $designation->delete();

        return $this->successResponse(null, 'Designation deleted successfully');
    }
}
