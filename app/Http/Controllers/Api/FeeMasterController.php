<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\FeeMaster;

class FeeMasterController extends \App\Http\Controllers\Api\Controller
{
    public function __construct()
    {
        $this->setControllerName('FeeMasterController');
    }

    public function index(Request $request): JsonResponse
    {
        $query = FeeMaster::query();

        if ($request->filled('session_id')) {
            $query->where('session_id', $request->session_id);
        }

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('feetype_id')) {
            $query->where('feetype_id', $request->feetype_id);
        }

        $feeMasters = $query->get();
        return $this->successResponse($feeMasters);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_id' => 'required|integer|exists:sessions,id',
            'feetype_id' => 'required|integer|exists:feetype,id',
            'class_id' => 'required|integer|exists:classes,id',
            'amount' => 'required|numeric|min:0',
            'feemaster_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'fine_type' => 'nullable|string',
            'fine_amount' => 'nullable|numeric|min:0',
            'fine_percentage' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'nullable|string',
        ]);

        $feeMaster = FeeMaster::create($validated);
        return $this->successResponse($feeMaster, 'Fee master created successfully');
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $feeMaster = FeeMaster::with(['session', 'feeType', 'class'])->find($id);

        if (!$feeMaster) {
            return $this->errorResponse('Fee master not found', statusCode: 404);
        }

        return $this->successResponse($feeMaster);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $feeMaster = FeeMaster::find($id);

        if (!$feeMaster) {
            return $this->errorResponse('Fee master not found', statusCode: 404);
        }

        $validated = $request->validate([
            'session_id' => 'sometimes|required|integer|exists:sessions,id',
            'feetype_id' => 'sometimes|required|integer|exists:feetype,id',
            'class_id' => 'sometimes|required|integer|exists:classes,id',
            'amount' => 'sometimes|required|numeric|min:0',
            'feemaster_name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'fine_type' => 'nullable|string',
            'fine_amount' => 'nullable|numeric|min:0',
            'fine_percentage' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'nullable|string',
        ]);

        $feeMaster->update($validated);
        return $this->successResponse($feeMaster, 'Fee master updated successfully');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $feeMaster = FeeMaster::find($id);

        if (!$feeMaster) {
            return $this->errorResponse('Fee master not found', statusCode: 404);
        }

        $feeMaster->delete();
        return $this->successResponse(message: 'Fee master deleted successfully');
    }
}
