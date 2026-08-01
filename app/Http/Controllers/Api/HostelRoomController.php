<?php

namespace App\Http\Controllers\Api;

use App\Models\HostelRoom;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HostelRoomController extends \App\Http\Controllers\Api\Controller
{
    public function __construct()
    {
        $this->setControllerName('HostelRoomController');
    }

    public function index(Request $request): JsonResponse
    {
        $query = HostelRoom::query();

        if ($request->has('hostel_id') && $request->hostel_id !== '') {
            $query->where('hostel_id', $request->hostel_id);
        }

        if ($request->has('room_type_id') && $request->room_type_id !== '') {
            $query->where('room_type_id', $request->room_type_id);
        }

        $listroom = $query->get();

        return $this->successResponse(['listroom' => $listroom]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'hostel_id' => 'required|integer|exists:hostel,id',
            'room_type_id' => 'required|integer|exists:room_types,id',
            'hostel_room_no' => 'required|string|max:255',
            'hostel_room_capacity' => 'required|integer|min:1',
            'cost_per_bed' => 'nullable|numeric|min:0',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $data = [
            'hostel_id' => $validated['hostel_id'],
            'room_type_id' => $validated['room_type_id'],
            'room_no' => $validated['hostel_room_no'],
            'no_of_bed' => $validated['hostel_room_capacity'],
            'cost_per_bed' => $validated['cost_per_bed'] ?? 0,
            'title' => $validated['title'] ?? null,
            'description' => $validated['description'] ?? null,
        ];

        $room = HostelRoom::create($data);

        return $this->successResponse(['room' => $room], 'Hostel room created successfully', 201);
    }

    public function show($id): JsonResponse
    {
        $room = HostelRoom::with(['hostel', 'roomType'])->find($id);

        if (!$room) {
            return $this->errorResponse('Hostel room not found', null, 404);
        }

        return $this->successResponse(['room' => $room]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $room = HostelRoom::find($id);

        if (!$room) {
            return $this->errorResponse('Hostel room not found', null, 404);
        }

        $validated = $request->validate([
            'hostel_id' => 'sometimes|required|integer|exists:hostel,id',
            'room_type_id' => 'sometimes|required|integer|exists:room_types,id',
            'hostel_room_no' => 'sometimes|required|string|max:255',
            'hostel_room_capacity' => 'sometimes|required|integer|min:1',
            'cost_per_bed' => 'nullable|numeric|min:0',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $data = [];
        if (isset($validated['hostel_id'])) {
            $data['hostel_id'] = $validated['hostel_id'];
        }
        if (isset($validated['room_type_id'])) {
            $data['room_type_id'] = $validated['room_type_id'];
        }
        if (isset($validated['hostel_room_no'])) {
            $data['room_no'] = $validated['hostel_room_no'];
        }
        if (isset($validated['hostel_room_capacity'])) {
            $data['no_of_bed'] = $validated['hostel_room_capacity'];
        }
        if (array_key_exists('cost_per_bed', $validated)) {
            $data['cost_per_bed'] = $validated['cost_per_bed'];
        }
        if (array_key_exists('title', $validated)) {
            $data['title'] = $validated['title'];
        }
        if (array_key_exists('description', $validated)) {
            $data['description'] = $validated['description'];
        }

        $room->update($data);

        return $this->successResponse(['room' => $room], 'Hostel room updated successfully');
    }

    public function destroy($id): JsonResponse
    {
        $room = HostelRoom::find($id);

        if (!$room) {
            return $this->errorResponse('Hostel room not found', null, 404);
        }

        $room->delete();

        return $this->successResponse(null, 'Hostel room deleted successfully');
    }
}
