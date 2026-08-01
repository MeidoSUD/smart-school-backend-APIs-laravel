<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\ExpenseHead;

class ExpenseHeadController extends \App\Http\Controllers\Api\Controller
{
    public function __construct()
    {
        $this->setControllerName('ExpenseHeadController');
    }

    public function index(Request $request): JsonResponse
    {
        $expenseHeads = ExpenseHead::all();
        return $this->successResponse($expenseHeads);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'expense_head' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|string',
        ]);

        $expenseHead = ExpenseHead::create($validated);
        return $this->successResponse($expenseHead, 'Expense head created successfully');
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $expenseHead = ExpenseHead::find($id);

        if (!$expenseHead) {
            return $this->errorResponse('Expense head not found', statusCode: 404);
        }

        return $this->successResponse($expenseHead);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $expenseHead = ExpenseHead::find($id);

        if (!$expenseHead) {
            return $this->errorResponse('Expense head not found', statusCode: 404);
        }

        $validated = $request->validate([
            'expense_head' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|string',
        ]);

        $expenseHead->update($validated);
        return $this->successResponse($expenseHead, 'Expense head updated successfully');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $expenseHead = ExpenseHead::find($id);

        if (!$expenseHead) {
            return $this->errorResponse('Expense head not found', statusCode: 404);
        }

        $expenseHead->delete();
        return $this->successResponse(message: 'Expense head deleted successfully');
    }
}
