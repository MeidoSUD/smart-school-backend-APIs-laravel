<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Homework;
use App\Models\Subject;
use App\Models\Classe;
use App\Models\Section;
use Illuminate\Http\Request;

class HomeworkController extends Controller
{
    public function index()
    {
        $homework = Homework::with(['subject', 'class', 'section'])->latest()->paginate(15);

        return view('homework.index', compact('homework'));
    }

    public function create()
    {
        $subjects = Subject::all();
        $classes = Classe::all();
        $sections = Section::all();

        return view('homework.create', compact('subjects', 'classes', 'sections'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject_id' => 'required|integer|exists:subjects,id',
            'class_id' => 'required|integer|exists:classes,id',
            'section_id' => 'required|integer|exists:sections,id',
            'homework_date' => 'required|date',
            'submission_date' => 'required|date',
            'marks' => 'nullable|integer',
            'description' => 'nullable|string',
        ]);

        Homework::create($validated);

        return redirect()->route('admin.homework.index')->with('success', 'Homework created successfully.');
    }

    public function edit(Homework $homework)
    {
        $subjects = Subject::all();
        $classes = Classe::all();
        $sections = Section::all();

        return view('homework.edit', compact('homework', 'subjects', 'classes', 'sections'));
    }

    public function update(Request $request, Homework $homework)
    {
        $validated = $request->validate([
            'subject_id' => 'required|integer|exists:subjects,id',
            'class_id' => 'required|integer|exists:classes,id',
            'section_id' => 'required|integer|exists:sections,id',
            'homework_date' => 'required|date',
            'submission_date' => 'required|date',
            'marks' => 'nullable|integer',
            'description' => 'nullable|string',
        ]);

        $homework->update($validated);

        return redirect()->route('admin.homework.index')->with('success', 'Homework updated successfully.');
    }

    public function destroy(Homework $homework)
    {
        $homework->delete();

        return redirect()->route('admin.homework.index')->with('success', 'Homework deleted successfully.');
    }
}
