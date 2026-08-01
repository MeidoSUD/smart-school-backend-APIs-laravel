<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Section;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    public function index()
    {
        $sections = Section::latest()->paginate(15);

        return view('sections.index', compact('sections'));
    }

    public function create()
    {
        return view('sections.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'section' => 'required|string|max:255',
            'is_active' => 'nullable|integer',
        ]);

        Section::create($validated);

        return redirect()->route('admin.sections.index')->with('success', 'Section created successfully.');
    }

    public function edit(Section $section)
    {
        return view('sections.edit', compact('section'));
    }

    public function update(Request $request, Section $section)
    {
        $validated = $request->validate([
            'section' => 'required|string|max:255',
            'is_active' => 'nullable|integer',
        ]);

        $section->update($validated);

        return redirect()->route('admin.sections.index')->with('success', 'Section updated successfully.');
    }

    public function destroy(Section $section)
    {
        $section->delete();

        return redirect()->route('admin.sections.index')->with('success', 'Section deleted successfully.');
    }
}
