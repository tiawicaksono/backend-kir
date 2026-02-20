<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function me(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Ambil semua role id milik user
        $roleIds = $user->roles()->pluck('roles.id');

        // Ambil menu berdasarkan semua role user
        $menus = Menu::whereHas('roles', function ($q) use ($roleIds) {
            $q->whereIn('roles.id', $roleIds);
        })
            ->whereNotIn('id', function ($query) use ($user) {
                $query->select('menu_id')
                    ->from('m_user_menus')
                    ->where('user_id', $user->id);
            })
            ->orderBy('order')
            ->distinct()
            ->get()
            ->map(fn($menu) => (object) $menu->toArray());

        $tree = $this->buildTree($menus);

        return response()->json($tree);
    }

    /**
     * Build parent-child tree tanpa duplikat
     */
    private function buildTree($menus)
    {
        $lookup = [];

        foreach ($menus as $menu) {
            $menu->children = [];
            $lookup[$menu->id] = $menu;
        }

        foreach ($menus as $menu) {
            if ($menu->parent_id !== null && isset($lookup[$menu->parent_id])) {
                $lookup[$menu->parent_id]->children[] = $menu;
            }
        }

        $tree = [];
        foreach ($menus as $menu) {
            if ($menu->parent_id === null) {
                if ($menu->route || !empty($menu->children)) {
                    $tree[] = $menu;
                }
            }
        }

        return $tree;
    }
}
