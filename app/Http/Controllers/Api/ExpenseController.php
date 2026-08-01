<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Expense;

class ExpenseController extends \App\Http\Controllers\Api\Controller
{
    public function __construct()
    {
        $this->setControllerName('ExpenseController');
    }

    public function index(Request $request): JsonResponse
    {
        $query = Expense::with('expenseHead');

        if ($request->filled('exp_head_id')) {
            $query->where('exp_head_id', $request->exp_head_id);
        }

        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        $expenses = $query->get();
        return $this->successResponse($expenses);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'exp_head_id' => 'required|integer|exists:expense_head,id',
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'is_active' => 'nullable|string',
        ]);

        $expense = Expense::create($validated);
        return $this->successResponse($expense, 'Expense created successfully');
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $expense = Expense::with('expenseHead')->find($id);

        if (!$expense) {
            return $this->errorResponse('Expense not found', statusCode: 404);
        }

        return $this->successResponse($expense);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $expense = Expense::find($id);

        if (!$expense) {
            return $this->errorResponse('Expense not found', statusCode: 404);
        }

        $validated = $request->validate([
            'exp_head_id' => 'sometimes|required|integer|exists:expense_head,id',
            'name' => 'sometimes|required|string|max:255',
            'date' => 'sometimes|required|date',
            'amount' => 'sometimes|required|numeric|min:0',
            'is_active' => 'nullable|string',
        ]);

        $expense->update($validated);
        return $this->successResponse($expense, 'Expense updated successfully');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $expense = Expense::find($id);

        if (!$expense) {
            return $this->errorResponse('Expense not found', statusCode: 404);
        }

        $expense->delete();
        return $this->successResponse(message: 'Expense deleted successfully');
    }
}
