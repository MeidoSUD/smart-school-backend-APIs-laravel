<?php
namespace App\Http\Controllers\Api;

use App\Models\Session;
use App\Http\Resources\SessionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SessionController extends \App\Http\Controllers\Api\Controller
{
    public function __construct()
    {
        $this->setControllerName('SessionController');
    }

    public function index(): JsonResponse
    {
        $sessions = Session::all();
        return $this->successResponse(SessionResource::collection($sessions));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'session' => 'required|string|max:20|unique:sessions,session',
        ]);

        $session = Session::create($request->only('session', 'is_active'));

        return $this->successResponse(new SessionResource($session), 'Session created', 201);
    }

    public function show(Session $session): JsonResponse
    {
        return $this->successResponse(new SessionResource($session));
    }

    public function update(Request $request, Session $session): JsonResponse
    {
        $request->validate([
            'session' => 'required|string|max:20|unique:sessions,session,' . $session->id,
        ]);

        $session->update($request->only('session', 'is_active'));

        return $this->successResponse(new SessionResource($session), 'Session updated');
    }

    public function destroy(Session $session): JsonResponse
    {
        $session->delete();
        return $this->successResponse(null, 'Session deleted');
    }
}
