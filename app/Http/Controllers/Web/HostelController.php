<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Hostel;
use Illuminate\Http\Request;

class HostelController extends Controller
{
    public function index()
    {
        $hostels = Hostel::latest()->paginate(15);

        return view('hostels.index', compact('hostels'));
    }

    public function create()
    {
        return view('hostels.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'hostel_name' => 'required|string|max:255',
            'type' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'intake' => 'nullable|integer',
            'description' => 'nullable|string',
            'is_active' => 'nullable|integer',
        ]);

        Hostel::create($validated);

        return redirect()->route('admin.hostels.index')->with('success', 'Hostel created successfully.');
    }

    public function edit(Hostel $hostel)
    {
        return view('hostels.edit', compact('hostel'));
    }

    public function update(Request $request, Hostel $hostel)
    {
        $validated = $request->validate([
            'hostel_name' => 'required|string|max:255',
            'type' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'intake' => 'nullable|integer',
            'description' => 'nullable|string',
            'is_active' => 'nullable|integer',
        ]);

        $hostel->update($validated);

        return redirect()->route('admin.hostels.index')->with('success', 'Hostel updated successfully.');
    }

    public function destroy(Hostel $hostel)
    {
        $hostel->delete();

        return redirect()->route('admin.hostels.index')->with('success', 'Hostel deleted successfully.');
    }
}
