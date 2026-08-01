<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Classe;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    public function index()
    {
        $classes = Classe::latest()->paginate(15);

        return view('classes.index', compact('classes'));
    }

    public function create()
    {
        return view('classes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'class' => 'required|string|max:255',
            'is_active' => 'nullable|integer',
        ]);

        Classe::create($validated);

        return redirect()->route('admin.classes.index')->with('success', 'Class created successfully.');
    }

    public function edit(Classe $class)
    {
        return view('classes.edit', compact('class'));
    }

    public function update(Request $request, Classe $class)
    {
        $validated = $request->validate([
            'class' => 'required|string|max:255',
            'is_active' => 'nullable|integer',
        ]);

        $class->update($validated);

        return redirect()->route('admin.classes.index')->with('success', 'Class updated successfully.');
    }

    public function destroy(Classe $class)
    {
        $class->delete();

        return redirect()->route('admin.classes.index')->with('success', 'Class deleted successfully.');
    }
}
