<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Models\StaffDesignation;
use App\Models\Department;
use Illuminate\Http\Request;

class StaffWebController extends Controller
{
    public function index(Request $request)
    {
        $query = Staff::with(['designation', 'department']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('staff_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        $staff = $query->latest()->paginate(15)->withQueryString();

        return view('staff.index', compact('staff'));
    }

    public function create()
    {
        $designations = StaffDesignation::all();
        $departments = Department::all();

        return view('staff.create', compact('designations', 'departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'staff_name' => 'required|string|max:255',
            'employee_id' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'gender' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'staff_designation_id' => 'nullable|integer',
            'department_id' => 'nullable|integer',
            'qualification' => 'nullable|string|max:255',
            'employee_salary' => 'nullable|numeric',
            'address' => 'nullable|string',
            'is_active' => 'nullable|integer',
        ]);

        Staff::create($validated);

        return redirect()->route('admin.staff.index')->with('success', 'Staff created successfully.');
    }

    public function show(Staff $member)
    {
        $member->load(['designation', 'department']);

        return view('staff.show', compact('member'));
    }

    public function edit(Staff $member)
    {
        $designations = StaffDesignation::all();
        $departments = Department::all();

        return view('staff.edit', compact('member', 'designations', 'departments'));
    }

    public function update(Request $request, Staff $member)
    {
        $validated = $request->validate([
            'staff_name' => 'required|string|max:255',
            'employee_id' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'gender' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'staff_designation_id' => 'nullable|integer',
            'department_id' => 'nullable|integer',
            'qualification' => 'nullable|string|max:255',
            'employee_salary' => 'nullable|numeric',
            'address' => 'nullable|string',
            'is_active' => 'nullable|integer',
        ]);

        $member->update($validated);

        return redirect()->route('admin.staff.index')->with('success', 'Staff updated successfully.');
    }

    public function destroy(Staff $member)
    {
        $member->delete();

        return redirect()->route('admin.staff.index')->with('success', 'Staff deleted successfully.');
    }
}
