<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Sanctum\PersonalAccessToken;

class ApiTokenAuthenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !preg_match('/Bearer\s+(.+)$/i', $authHeader, $matches)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. Token not provided.',
            ], 401);
        }

        $token = PersonalAccessToken::findToken($matches[1]);

        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or expired token.',
            ], 401);
        }

        $user = $token->tokenable;

        if (!$user->isActive()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Your account is disabled. Please contact administrator.',
            ], 403);
        }

        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        $request->merge(['auth_user' => $user]);

        return $next($request);
    }
}
