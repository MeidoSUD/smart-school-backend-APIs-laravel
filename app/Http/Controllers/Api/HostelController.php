<?php

namespace App\Http\Controllers\Api;

use App\Models\Hostel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HostelController extends \App\Http\Controllers\Api\Controller
{
    public function __construct()
    {
        $this->setControllerName('HostelController');
    }

    public function index(): JsonResponse
    {
        $listhostel = Hostel::where('is_active', 'yes')
            ->withCount('rooms')
            ->get();

        return $this->successResponse(['listhostel' => $listhostel]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'hostel_name' => 'required|string|max:255',
            'hostel_type' => 'required|string|max:255',
            'contact_no' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'intake' => 'nullable|integer',
            'description' => 'nullable|string',
        ]);

        $data = [
            'hostel_name' => $validated['hostel_name'],
            'type' => $validated['hostel_type'],
            'address' => $validated['address'] ?? null,
            'intake' => $validated['intake'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_active' => 'yes',
        ];

        $hostel = Hostel::create($data);

        return $this->successResponse(['hostel' => $hostel], 'Hostel created successfully', 201);
    }

    public function show($id): JsonResponse
    {
        $hostel = Hostel::with('rooms')->find($id);

        if (!$hostel) {
            return $this->errorResponse('Hostel not found', null, 404);
        }

        return $this->successResponse(['hostel' => $hostel]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $hostel = Hostel::find($id);

        if (!$hostel) {
            return $this->errorResponse('Hostel not found', null, 404);
        }

        $validated = $request->validate([
            'hostel_name' => 'sometimes|required|string|max:255',
            'hostel_type' => 'sometimes|required|string|max:255',
            'contact_no' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'intake' => 'nullable|integer',
            'description' => 'nullable|string',
        ]);

        $data = [];
        if (isset($validated['hostel_name'])) {
            $data['hostel_name'] = $validated['hostel_name'];
        }
        if (isset($validated['hostel_type'])) {
            $data['type'] = $validated['hostel_type'];
        }
        if (array_key_exists('address', $validated)) {
            $data['address'] = $validated['address'];
        }
        if (isset($validated['intake'])) {
            $data['intake'] = $validated['intake'];
        }
        if (array_key_exists('description', $validated)) {
            $data['description'] = $validated['description'];
        }

        $hostel->update($data);

        return $this->successResponse(['hostel' => $hostel], 'Hostel updated successfully');
    }

    public function destroy($id): JsonResponse
    {
        $hostel = Hostel::find($id);

        if (!$hostel) {
            return $this->errorResponse('Hostel not found', null, 404);
        }

        $hostel->delete();

        return $this->successResponse(null, 'Hostel deleted successfully');
    }
}
