<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Income;
use App\Models\IncomeHead;
use Illuminate\Http\Request;

class IncomeController extends Controller
{
    public function index()
    {
        $incomes = Income::with('incomeHead')->latest()->paginate(15);

        return view('incomes.index', compact('incomes'));
    }

    public function create()
    {
        $incomeHeads = IncomeHead::all();

        return view('incomes.create', compact('incomeHeads'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'income_head_id' => 'nullable|integer',
            'date' => 'nullable|date',
            'amount' => 'required|numeric',
            'is_active' => 'nullable|integer',
        ]);

        Income::create($validated);

        return redirect()->route('admin.incomes.index')->with('success', 'Income created successfully.');
    }

    public function edit(Income $income)
    {
        $incomeHeads = IncomeHead::all();

        return view('incomes.edit', compact('income', 'incomeHeads'));
    }

    public function update(Request $request, Income $income)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'income_head_id' => 'nullable|integer',
            'date' => 'nullable|date',
            'amount' => 'required|numeric',
            'is_active' => 'nullable|integer',
        ]);

        $income->update($validated);

        return redirect()->route('admin.incomes.index')->with('success', 'Income updated successfully.');
    }

    public function destroy(Income $income)
    {
        $income->delete();

        return redirect()->route('admin.incomes.index')->with('success', 'Income deleted successfully.');
    }
}
