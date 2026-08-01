<?php

namespace App\Http\Controllers\Api;

use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VehicleController extends \App\Http\Controllers\Api\Controller
{
    public function __construct()
    {
        $this->setControllerName('VehicleController');
    }

    public function index(Request $request): JsonResponse
    {
        $query = Vehicle::query();

        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('vehicle_no', 'LIKE', "%{$search}%")
                    ->orWhere('vehicle_model', 'LIKE', "%{$search}%")
                    ->orWhere('driver_name', 'LIKE', "%{$search}%");
            });
        }

        $vehicles = $query->get();

        return $this->successResponse(['listvehicle' => $vehicles]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'vehicle_no' => 'required|string|max:255',
            'vehicle_model' => 'required|string|max:255',
            'driver_name' => 'required|string|max:255',
            'driver_mobile_no' => 'required|string|max:255',
            'vehicle_photo' => 'nullable|string|max:255',
            'manufacture_year' => 'nullable|string|max:4',
            'registration_number' => 'nullable|string|max:255',
            'chasis_number' => 'nullable|string|max:255',
            'max_seating_capacity' => 'nullable|integer',
            'driver_licence' => 'nullable|string|max:255',
            'note' => 'nullable|string',
        ]);

        $validated['driver_contact'] = $validated['driver_mobile_no'];
        unset($validated['driver_mobile_no']);

        $vehicle = Vehicle::create($validated);

        return $this->successResponse(['vehicle' => $vehicle], 'Vehicle created successfully', 201);
    }

    public function show($id): JsonResponse
    {
        $vehicle = Vehicle::find($id);

        if (!$vehicle) {
            return $this->errorResponse('Vehicle not found', null, 404);
        }

        return $this->successResponse(['vehicle' => $vehicle]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $vehicle = Vehicle::find($id);

        if (!$vehicle) {
            return $this->errorResponse('Vehicle not found', null, 404);
        }

        $validated = $request->validate([
            'vehicle_no' => 'sometimes|required|string|max:255',
            'vehicle_model' => 'sometimes|required|string|max:255',
            'driver_name' => 'sometimes|required|string|max:255',
            'driver_mobile_no' => 'sometimes|required|string|max:255',
            'vehicle_photo' => 'nullable|string|max:255',
            'manufacture_year' => 'nullable|string|max:4',
            'registration_number' => 'nullable|string|max:255',
            'chasis_number' => 'nullable|string|max:255',
            'max_seating_capacity' => 'nullable|integer',
            'driver_licence' => 'nullable|string|max:255',
            'note' => 'nullable|string',
        ]);

        if (isset($validated['driver_mobile_no'])) {
            $validated['driver_contact'] = $validated['driver_mobile_no'];
            unset($validated['driver_mobile_no']);
        }

        $vehicle->update($validated);

        return $this->successResponse(['vehicle' => $vehicle], 'Vehicle updated successfully');
    }

    public function destroy($id): JsonResponse
    {
        $vehicle = Vehicle::find($id);

        if (!$vehicle) {
            return $this->errorResponse('Vehicle not found', null, 404);
        }

        $vehicle->delete();

        return $this->successResponse(null, 'Vehicle deleted successfully');
    }
}
