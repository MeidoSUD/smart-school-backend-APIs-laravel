<?php

namespace App\Http\Controllers\Api;

use App\Models\RoomType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoomTypeController extends \App\Http\Controllers\Api\Controller
{
    public function __construct()
    {
        $this->setControllerName('RoomTypeController');
    }

    public function index(): JsonResponse
    {
        $roomTypes = RoomType::all();

        return $this->successResponse(['listroomtype' => $roomTypes]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'room_type' => 'required|string|max:255',
            'capacity' => 'nullable|integer|min:1',
            'rent_per_room' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $roomType = RoomType::create([
            'room_type' => $validated['room_type'],
            'description' => $validated['description'] ?? null,
        ]);

        return $this->successResponse(['room_type' => $roomType], 'Room type created successfully', 201);
    }

    public function show($id): JsonResponse
    {
        $roomType = RoomType::find($id);

        if (!$roomType) {
            return $this->errorResponse('Room type not found', null, 404);
        }

        return $this->successResponse(['room_type' => $roomType]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $roomType = RoomType::find($id);

        if (!$roomType) {
            return $this->errorResponse('Room type not found', null, 404);
        }

        $validated = $request->validate([
            'room_type' => 'sometimes|required|string|max:255',
            'capacity' => 'nullable|integer|min:1',
            'rent_per_room' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $data = [];
        if (isset($validated['room_type'])) {
            $data['room_type'] = $validated['room_type'];
        }
        if (array_key_exists('description', $validated)) {
            $data['description'] = $validated['description'];
        }

        $roomType->update($data);

        return $this->successResponse(['room_type' => $roomType], 'Room type updated successfully');
    }

    public function destroy($id): JsonResponse
    {
        $roomType = RoomType::find($id);

        if (!$roomType) {
            return $this->errorResponse('Room type not found', null, 404);
        }

        $roomType->delete();

        return $this->successResponse(null, 'Room type deleted successfully');
    }
}
