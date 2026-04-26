<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class ApiTokenAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * Validates API token from Authorization header or token query/post parameter
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $this->getTokenFromRequest($request);

        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. Token not provided.',
            ], 401);
        }

        // Find user by token
        $user = User::where('token', $token)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or expired token.',
            ], 401);
        }

        // Check if user is active
        if (!$user->isActive()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Your account is disabled. Please contact administrator.',
            ], 403);
        }

        // Set the authenticated user on the request
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        // Also set as traditional user() for compatibility
        $request->merge(['auth_user' => $user]);

        return $next($request);
    }

    /**
     * Extract token from various sources
     *
     * @param Request $request
     * @return string|null
     */
    private function getTokenFromRequest(Request $request): ?string
    {
        // 1. Check Authorization header (Bearer token)
        $authHeader = $request->header('Authorization');
        if ($authHeader && preg_match('/Bearer\s+(.+)$/i', $authHeader, $matches)) {
            return $matches[1];
        }

        // 2. Check X-Token header
        $xToken = $request->header('X-Token');
        if ($xToken) {
            return $xToken;
        }

        // 3. Check query parameter
        $tokenQuery = $request->query('token');
        if ($tokenQuery) {
            return $tokenQuery;
        }

        // 4. Check POST parameter
        $tokenPost = $request->input('token');
        if ($tokenPost) {
            return $tokenPost;
        }

        return null;
    }
}