<?php

namespace App\Http\Controllers\Api;

use App\Models\TransportRoute;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransportRouteController extends \App\Http\Controllers\Api\Controller
{
    public function __construct()
    {
        $this->setControllerName('TransportRouteController');
    }

    public function index(): JsonResponse
    {
        $routes = TransportRoute::where('is_active', 'yes')->get();

        return $this->successResponse(['listroute' => $routes]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'route_name' => 'required|string|max:255',
            'route_pickup_point' => 'nullable|string|max:255',
            'route_droping_point' => 'nullable|string|max:255',
            'no_of_vehicle' => 'nullable|integer',
            'note' => 'nullable|string',
        ]);

        $data = [
            'route_title' => $validated['route_name'],
            'no_of_vehicle' => $validated['no_of_vehicle'] ?? 0,
            'note' => $validated['note'] ?? null,
            'is_active' => 'yes',
        ];

        if (isset($validated['route_pickup_point']) || isset($validated['route_droping_point'])) {
            $pickup = $validated['route_pickup_point'] ?? '';
            $drop = $validated['route_droping_point'] ?? '';
            $data['note'] = trim($pickup . ' - ' . $drop, ' -') ?: ($data['note'] ?? null);
        }

        $route = TransportRoute::create($data);

        return $this->successResponse(['route' => $route], 'Transport route created successfully', 201);
    }

    public function show($id): JsonResponse
    {
        $route = TransportRoute::find($id);

        if (!$route) {
            return $this->errorResponse('Transport route not found', null, 404);
        }

        return $this->successResponse(['route' => $route]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $route = TransportRoute::find($id);

        if (!$route) {
            return $this->errorResponse('Transport route not found', null, 404);
        }

        $validated = $request->validate([
            'route_name' => 'sometimes|required|string|max:255',
            'route_pickup_point' => 'nullable|string|max:255',
            'route_droping_point' => 'nullable|string|max:255',
            'no_of_vehicle' => 'nullable|integer',
            'note' => 'nullable|string',
        ]);

        $data = [];
        if (isset($validated['route_name'])) {
            $data['route_title'] = $validated['route_name'];
        }
        if (isset($validated['no_of_vehicle'])) {
            $data['no_of_vehicle'] = $validated['no_of_vehicle'];
        }
        if (isset($validated['note'])) {
            $data['note'] = $validated['note'];
        }

        if (isset($validated['route_pickup_point']) || isset($validated['route_droping_point'])) {
            $pickup = $validated['route_pickup_point'] ?? '';
            $drop = $validated['route_droping_point'] ?? '';
            $data['note'] = trim($pickup . ' - ' . $drop, ' -');
        }

        $route->update($data);

        return $this->successResponse(['route' => $route], 'Transport route updated successfully');
    }

    public function destroy($id): JsonResponse
    {
        $route = TransportRoute::find($id);

        if (!$route) {
            return $this->errorResponse('Transport route not found', null, 404);
        }

        $route->delete();

        return $this->successResponse(null, 'Transport route deleted successfully');
    }
}
