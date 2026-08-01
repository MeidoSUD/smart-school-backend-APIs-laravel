<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Income;

class IncomeController extends \App\Http\Controllers\Api\Controller
{
    public function __construct()
    {
        $this->setControllerName('IncomeController');
    }

    public function index(Request $request): JsonResponse
    {
        $query = Income::with('incomeHead');

        if ($request->filled('income_head_id')) {
            $query->where('income_head_id', $request->income_head_id);
        }

        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        $incomes = $query->get();
        return $this->successResponse($incomes);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'income_head_id' => 'required|integer|exists:income_head,id',
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'is_active' => 'nullable|string',
        ]);

        $income = Income::create($validated);
        return $this->successResponse($income, 'Income created successfully');
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $income = Income::with('incomeHead')->find($id);

        if (!$income) {
            return $this->errorResponse('Income not found', statusCode: 404);
        }

        return $this->successResponse($income);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $income = Income::find($id);

        if (!$income) {
            return $this->errorResponse('Income not found', statusCode: 404);
        }

        $validated = $request->validate([
            'income_head_id' => 'sometimes|required|integer|exists:income_head,id',
            'name' => 'sometimes|required|string|max:255',
            'date' => 'sometimes|required|date',
            'amount' => 'sometimes|required|numeric|min:0',
            'is_active' => 'nullable|string',
        ]);

        $income->update($validated);
        return $this->successResponse($income, 'Income updated successfully');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $income = Income::find($id);

        if (!$income) {
            return $this->errorResponse('Income not found', statusCode: 404);
        }

        $income->delete();
        return $this->successResponse(message: 'Income deleted successfully');
    }
}
