<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Transport;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index()
    {
        $vehicles = Transport::latest()->paginate(15);

        return view('vehicles.index', compact('vehicles'));
    }

    public function create()
    {
        return view('vehicles.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_no' => 'nullable|string|max:255',
            'vehicle_type' => 'nullable|string|max:255',
            'capacity' => 'nullable|integer',
            'driver_name' => 'nullable|string|max:255',
            'driver_phone' => 'nullable|string|max:255',
        ]);

        Transport::create($validated);

        return redirect()->route('admin.vehicles.index')->with('success', 'Vehicle created successfully.');
    }

    public function edit(Transport $vehicle)
    {
        return view('vehicles.edit', compact('vehicle'));
    }

    public function update(Request $request, Transport $vehicle)
    {
        $validated = $request->validate([
            'vehicle_no' => 'nullable|string|max:255',
            'vehicle_type' => 'nullable|string|max:255',
            'capacity' => 'nullable|integer',
            'driver_name' => 'nullable|string|max:255',
            'driver_phone' => 'nullable|string|max:255',
        ]);

        $vehicle->update($validated);

        return redirect()->route('admin.vehicles.index')->with('success', 'Vehicle updated successfully.');
    }

    public function destroy(Transport $vehicle)
    {
        $vehicle->delete();

        return redirect()->route('admin.vehicles.index')->with('success', 'Vehicle deleted successfully.');
    }
}
