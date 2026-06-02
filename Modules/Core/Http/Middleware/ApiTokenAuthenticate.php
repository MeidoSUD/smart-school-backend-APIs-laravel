<?php

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Modules\Core\Entities\User;

class ApiTokenAuthenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $this->getTokenFromRequest($request);

        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. Token not provided.',
            ], 401);
        }

        $user = User::where('token', $token)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or expired token.',
            ], 401);
        }

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

    private function getTokenFromRequest(Request $request): ?string
    {
        $authHeader = $request->header('Authorization');
        if ($authHeader && preg_match('/Bearer\s+(.+)$/i', $authHeader, $matches)) {
            return $matches[1];
        }

        $xToken = $request->header('X-Token');
        if ($xToken) {
            return $xToken;
        }

        $tokenQuery = $request->query('token');
        if ($tokenQuery) {
            return $tokenQuery;
        }

        $tokenPost = $request->input('token');
        if ($tokenPost) {
            return $tokenPost;
        }

        return null;
    }
}
