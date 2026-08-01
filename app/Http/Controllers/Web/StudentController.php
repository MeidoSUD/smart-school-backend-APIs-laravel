<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('First_name', 'like', "%{$search}%")
                  ->orWhere('Last_name', 'like', "%{$search}%")
                  ->orWhere('admission_no', 'like', "%{$search}%")
                  ->orWhere('student_email', 'like', "%{$search}%");
            });
        }

        $students = $query->latest()->paginate(15)->withQueryString();

        return view('students.index', compact('students'));
    }

    public function create()
    {
        return view('students.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'admission_no' => 'required|string|max:255',
            'First_name' => 'required|string|max:255',
            'Last_name' => 'required|string|max:255',
            'student_email' => 'nullable|email|max:255',
            'student_phone' => 'nullable|string|max:255',
            'student_gender' => 'nullable|string|max:255',
            'dob' => 'nullable|date',
            'blood_group' => 'nullable|string|max:255',
            'religion' => 'nullable|string|max:255',
            'Father_name' => 'nullable|string|max:255',
            'Father_phone' => 'nullable|string|max:255',
            'Mother_name' => 'nullable|string|max:255',
            'Mother_phone' => 'nullable|string|max:255',
            'student_address' => 'nullable|string',
            'is_active' => 'nullable|string|max:255',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? 'active';

        Student::create($validated);

        return redirect()->route('admin.students.index')->with('success', 'Student created successfully.');
    }

    public function show(Student $student)
    {
        $student->load('studentSessions.class', 'studentSessions.section');

        return view('students.show', compact('student'));
    }

    public function edit(Student $student)
    {
        return view('students.edit', compact('student'));
    }

    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'admission_no' => 'required|string|max:255',
            'First_name' => 'required|string|max:255',
            'Last_name' => 'required|string|max:255',
            'student_email' => 'nullable|email|max:255',
            'student_phone' => 'nullable|string|max:255',
            'student_gender' => 'nullable|string|max:255',
            'dob' => 'nullable|date',
            'blood_group' => 'nullable|string|max:255',
            'religion' => 'nullable|string|max:255',
            'Father_name' => 'nullable|string|max:255',
            'Father_phone' => 'nullable|string|max:255',
            'Mother_name' => 'nullable|string|max:255',
            'Mother_phone' => 'nullable|string|max:255',
            'student_address' => 'nullable|string',
            'is_active' => 'nullable|string|max:255',
        ]);

        $student->update($validated);

        return redirect()->route('admin.students.index')->with('success', 'Student updated successfully.');
    }

    public function destroy(Student $student)
    {
        $student->delete();

        return redirect()->route('admin.students.index')->with('success', 'Student deleted successfully.');
    }
}
