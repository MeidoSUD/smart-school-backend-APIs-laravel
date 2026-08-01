<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Role;
use Symfony\Component\HttpFoundation\Response;

class RequirePermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Check via role relationship to roles table
        $role = Role::where('name', $user->role)->first();

        if (!$role) {
            return response()->json(['message' => 'Unauthorized: no role found'], 403);
        }

        // Superadmin bypass
        if ($role->is_superadmin == 1) {
            return $next($request);
        }

        // Check if role has the permission category
        $hasPermission = $role->permissionCategories()
            ->where('permission_category.name', $permission)
            ->where('roles_permissions.is_active', 1)
            ->exists();

        if (!$hasPermission) {
            return response()->json(['message' => "Unauthorized: missing permission '$permission'"], 403);
        }

        return $next($request);
    }
}
