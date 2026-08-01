<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function index()
    {
        $exams = Exam::latest()->paginate(15);

        return view('exams.index', compact('exams'));
    }

    public function create()
    {
        return view('exams.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'note' => 'nullable|string|max:255',
            'is_active' => 'nullable|integer',
        ]);

        Exam::create($validated);

        return redirect()->route('admin.exams.index')->with('success', 'Exam created successfully.');
    }

    public function edit(Exam $exam)
    {
        return view('exams.edit', compact('exam'));
    }

    public function update(Request $request, Exam $exam)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'note' => 'nullable|string|max:255',
            'is_active' => 'nullable|integer',
        ]);

        $exam->update($validated);

        return redirect()->route('admin.exams.index')->with('success', 'Exam updated successfully.');
    }

    public function destroy(Exam $exam)
    {
        $exam->delete();

        return redirect()->route('admin.exams.index')->with('success', 'Exam deleted successfully.');
    }
}
