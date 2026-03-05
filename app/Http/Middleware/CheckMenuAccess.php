<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Menu;
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

        // action from HTTP method
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

        // normalize path (remove api prefix)
        $path = '/' . ltrim(str_replace('api/', '', $request->path()), '/');

        // find menu by route
        $menu = Menu::where('route', $path)
            ->where('is_active', true)
            ->first();

        // try parent route if not found
        if (!$menu) {
            $parent = dirname($path);
            if ($parent !== '/' && $parent !== '.') {
                $menu = Menu::where('route', $parent)
                    ->where('is_active', true)
                    ->first();
            }
        }

        logger()->info('menu-debug', [
            'path' => $path,
            'menu_route' => $menu?->route
        ]);

        // route not in menu → free access
        if (!$menu) {
            return $next($request);
        }

        // === BLACKLIST CHECK (m_user_menus) ===
        $blacklist = $user->menus()
            ->where('menu_id', $menu->id)
            ->exists();

        if ($blacklist) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // === PERMISSION TREE (getEffectiveMenus) ===
        $menus = $user->getEffectiveMenus();

        // flatten tree
        $flat = $this->flattenMenus($menus);

        // find permission for this menu
        $permission = collect($flat)->firstWhere('id', $menu->id);

        if (!$permission) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // === ACTION CHECK ===
        $field = "can_{$action}";

        if (isset($permission->$field) && !$permission->$field) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return $next($request);
    }

    /**
     * Flatten tree menu to single array
     */
    private function flattenMenus(array $menus): array
    {
        $flat = [];

        foreach ($menus as $menu) {
            $flat[] = $menu;

            if (!empty($menu->children)) {
                $flat = array_merge($flat, $this->flattenMenus($menu->children));
            }
        }

        return $flat;
    }
}
