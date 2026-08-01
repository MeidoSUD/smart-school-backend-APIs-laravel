<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index()
    {
        $subjects = Subject::latest()->paginate(15);

        return view('subjects.index', compact('subjects'));
    }

    public function create()
    {
        return view('subjects.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:255',
            'is_active' => 'nullable|integer',
        ]);

        Subject::create($validated);

        return redirect()->route('admin.subjects.index')->with('success', 'Subject created successfully.');
    }

    public function edit(Subject $subject)
    {
        return view('subjects.edit', compact('subject'));
    }

    public function update(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:255',
            'is_active' => 'nullable|integer',
        ]);

        $subject->update($validated);

        return redirect()->route('admin.subjects.index')->with('success', 'Subject updated successfully.');
    }

    public function destroy(Subject $subject)
    {
        $subject->delete();

        return redirect()->route('admin.subjects.index')->with('success', 'Subject deleted successfully.');
    }
}
