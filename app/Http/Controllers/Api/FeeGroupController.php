<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\FeeGroup;
use App\Models\FeeGroupsFeetype;

class FeeGroupController extends \App\Http\Controllers\Api\Controller
{
    public function __construct()
    {
        $this->setControllerName('FeeGroupController');
    }

    public function index(Request $request): JsonResponse
    {
        $feeGroups = FeeGroup::with('feeGroupsFeetype.feeType')->get();
        return $this->successResponse($feeGroups);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:fee_groups,name',
        ]);

        $feeGroup = FeeGroup::create($validated);
        return $this->successResponse($feeGroup, 'Fee group created successfully');
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $feeGroup = FeeGroup::with('feeGroupsFeetype.feeType')->find($id);

        if (!$feeGroup) {
            return $this->errorResponse('Fee group not found', statusCode: 404);
        }

        return $this->successResponse($feeGroup);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $feeGroup = FeeGroup::find($id);

        if (!$feeGroup) {
            return $this->errorResponse('Fee group not found', statusCode: 404);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('fee_groups', 'name')->ignore($id)],
        ]);

        $feeGroup->update($validated);
        return $this->successResponse($feeGroup, 'Fee group updated successfully');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $feeGroup = FeeGroup::find($id);

        if (!$feeGroup) {
            return $this->errorResponse('Fee group not found', statusCode: 404);
        }

        $feeGroup->delete();
        return $this->successResponse(message: 'Fee group deleted successfully');
    }
}
