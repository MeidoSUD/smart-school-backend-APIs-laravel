<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseHead;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index()
    {
        $expenses = Expense::with('expenseHead')->latest()->paginate(15);

        return view('expenses.index', compact('expenses'));
    }

    public function create()
    {
        $expenseHeads = ExpenseHead::all();

        return view('expenses.create', compact('expenseHeads'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'exp_head_id' => 'nullable|integer',
            'date' => 'nullable|date',
            'amount' => 'required|numeric',
            'is_active' => 'nullable|integer',
        ]);

        Expense::create($validated);

        return redirect()->route('admin.expenses.index')->with('success', 'Expense created successfully.');
    }

    public function edit(Expense $expense)
    {
        $expenseHeads = ExpenseHead::all();

        return view('expenses.edit', compact('expense', 'expenseHeads'));
    }

    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'exp_head_id' => 'nullable|integer',
            'date' => 'nullable|date',
            'amount' => 'required|numeric',
            'is_active' => 'nullable|integer',
        ]);

        $expense->update($validated);

        return redirect()->route('admin.expenses.index')->with('success', 'Expense updated successfully.');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();

        return redirect()->route('admin.expenses.index')->with('success', 'Expense deleted successfully.');
    }
}
