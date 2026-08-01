<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\IncomeHead;

class IncomeHeadController extends \App\Http\Controllers\Api\Controller
{
    public function __construct()
    {
        $this->setControllerName('IncomeHeadController');
    }

    public function index(Request $request): JsonResponse
    {
        $incomeHeads = IncomeHead::all();
        return $this->successResponse($incomeHeads);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'income_head' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|string',
        ]);

        $incomeHead = IncomeHead::create($validated);
        return $this->successResponse($incomeHead, 'Income head created successfully');
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $incomeHead = IncomeHead::find($id);

        if (!$incomeHead) {
            return $this->errorResponse('Income head not found', statusCode: 404);
        }

        return $this->successResponse($incomeHead);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $incomeHead = IncomeHead::find($id);

        if (!$incomeHead) {
            return $this->errorResponse('Income head not found', statusCode: 404);
        }

        $validated = $request->validate([
            'income_head' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|string',
        ]);

        $incomeHead->update($validated);
        return $this->successResponse($incomeHead, 'Income head updated successfully');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $incomeHead = IncomeHead::find($id);

        if (!$incomeHead) {
            return $this->errorResponse('Income head not found', statusCode: 404);
        }

        $incomeHead->delete();
        return $this->successResponse(message: 'Income head deleted successfully');
    }
}
