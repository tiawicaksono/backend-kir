<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Ambil role user dari pivot (yang aktif)
        $hasRole = $user->roles()
            ->whereIn('role_id', $roles)
            ->exists();

        if (!$hasRole) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return $next($request);
    }
}
