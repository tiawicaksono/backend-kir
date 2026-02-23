<?php

namespace App\Http\Middleware;

use App\Models\Menu;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMenuAccess
{
    public function handle(Request $request, Closure $next, $action = null): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Tentukan action berdasarkan HTTP method
        if (!$action) {
            $method = $request->method();
            $action = match ($method) {
                'GET'    => 'view',
                'POST'   => 'create',
                'PUT',
                'PATCH'  => 'update',
                'DELETE' => 'delete',
                default  => 'view',
            };
        }

        // Ambil path tanpa prefix 'api'
        $path = '/' . ltrim(str_replace('api/', '', $request->path()), '/');

        // Cari menu berdasarkan route
        $menu = Menu::where('route', $path)
            ->where('is_active', true)
            ->first();

        // Kalau route tidak ada di m_menus → anggap bebas
        if (!$menu) {
            return $next($request);
        }

        // Ambil semua menu efektif user (role + blacklist)
        $menus = $user->getEffectiveMenus();

        $permission = $menus->get($menu->id);

        // Kalau menu ada di blacklist (m_user_menus) → forbidden
        if (!$permission) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // Cek action berdasarkan role permission
        $field = "can_{$action}";

        if (isset($permission->$field) && !$permission->$field) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return $next($request);
    }
}
