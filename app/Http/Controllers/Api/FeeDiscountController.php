<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\FeeDiscount;

class FeeDiscountController extends \App\Http\Controllers\Api\Controller
{
    public function __construct()
    {
        $this->setControllerName('FeeDiscountController');
    }

    public function index(Request $request): JsonResponse
    {
        $query = FeeDiscount::query();

        if ($request->filled('session_id')) {
            $query->where('session_id', $request->session_id);
        }

        $discounts = $query->get();
        return $this->successResponse($discounts);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_id' => 'required|integer|exists:sessions,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255',
            'type' => 'nullable|string',
            'percentage' => 'required|numeric|min:0|max:100',
            'amount' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'nullable|string',
        ]);

        $discount = FeeDiscount::create($validated);
        return $this->successResponse($discount, 'Fee discount created successfully');
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $discount = FeeDiscount::find($id);

        if (!$discount) {
            return $this->errorResponse('Fee discount not found', statusCode: 404);
        }

        return $this->successResponse($discount);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $discount = FeeDiscount::find($id);

        if (!$discount) {
            return $this->errorResponse('Fee discount not found', statusCode: 404);
        }

        $validated = $request->validate([
            'session_id' => 'sometimes|required|integer|exists:sessions,id',
            'name' => 'sometimes|required|string|max:255',
            'code' => 'nullable|string|max:255',
            'type' => 'nullable|string',
            'percentage' => 'sometimes|required|numeric|min:0|max:100',
            'amount' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'nullable|string',
        ]);

        $discount->update($validated);
        return $this->successResponse($discount, 'Fee discount updated successfully');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $discount = FeeDiscount::find($id);

        if (!$discount) {
            return $this->errorResponse('Fee discount not found', statusCode: 404);
        }

        $discount->delete();
        return $this->successResponse(message: 'Fee discount deleted successfully');
    }
}
