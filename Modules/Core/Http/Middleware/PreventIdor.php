<?php

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PreventIdor
{
    public function handle(Request $request, Closure $next, string $modelClass, string $ownerColumn = 'user_id')
    {
        $user = $request->user();
        $resourceId = $request->route('id') ?? $request->route parameter('id');

        if (!$resourceId) {
            return $next($request);
        }

        if (in_array($user->role, ['admin', 'staff', 'teacher'])) {
            return $next($request);
        }

        $resource = $modelClass::find($resourceId);

        if (!$resource) {
            return $next($request);
        }

        $ownerId = $resource->{$ownerColumn};

        if ($user->role === 'student' && $ownerId !== $user->user_id) {
            abort(403, 'Unauthorized access to this resource');
        }

        if ($user->role === 'parent') {
            $childIds = \Modules\Academic\Entities\Student::where('parent_id', $user->id)
                ->pluck('id')
                ->toArray();

            if (!in_array($ownerId, $childIds)) {
                abort(403, 'Unauthorized access to this resource');
            }
        }

        return $next($request);
    }
}
