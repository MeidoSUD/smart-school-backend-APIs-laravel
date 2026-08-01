<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\FeeMaster;
use App\Models\Classe;
use Illuminate\Http\Request;

class FeeMasterController extends Controller
{
    public function index()
    {
        $feeMasters = FeeMaster::with(['class', 'feeType'])->latest()->paginate(15);

        return view('fees.index', compact('feeMasters'));
    }

    public function create()
    {
        $classes = Classe::all();

        return view('fees.create', compact('classes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'feemaster_name' => 'required|string|max:255',
            'class_id' => 'required|integer|exists:classes,id',
            'amount' => 'required|numeric',
            'due_date' => 'nullable|date',
            'fine_amount' => 'nullable|numeric',
            'description' => 'nullable|string',
            'is_active' => 'nullable|integer',
        ]);

        FeeMaster::create($validated);

        return redirect()->route('admin.fee-masters.index')->with('success', 'Fee master created successfully.');
    }

    public function edit(FeeMaster $feeMaster)
    {
        $classes = Classe::all();

        return view('fees.edit', compact('feeMaster', 'classes'));
    }

    public function update(Request $request, FeeMaster $feeMaster)
    {
        $validated = $request->validate([
            'feemaster_name' => 'required|string|max:255',
            'class_id' => 'required|integer|exists:classes,id',
            'amount' => 'required|numeric',
            'due_date' => 'nullable|date',
            'fine_amount' => 'nullable|numeric',
            'description' => 'nullable|string',
            'is_active' => 'nullable|integer',
        ]);

        $feeMaster->update($validated);

        return redirect()->route('admin.fee-masters.index')->with('success', 'Fee master updated successfully.');
    }

    public function destroy(FeeMaster $feeMaster)
    {
        $feeMaster->delete();

        return redirect()->route('admin.fee-masters.index')->with('success', 'Fee master deleted successfully.');
    }
}
