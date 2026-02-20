<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\User;

class MenuService
{
    public static function forUser(User $user, string $locale = 'id')
    {
        return Menu::query()
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id)
                    ->where('can_view', true);
            })
            ->with([
                'translations' => fn($q) => $q->where('locale', $locale),
                'children' => function ($q) use ($user, $locale) {
                    $q->whereHas('users', function ($q2) use ($user) {
                        $q2->where('users.id', $user->id)
                            ->where('can_view', true);
                    })
                        ->with([
                            'translations' => fn($t) => $t->where('locale', $locale),
                        ]);
                }
            ])
            ->orderBy('order')
            ->get()
            ->map(fn($menu) => self::mapMenu($menu));
    }

    protected static function mapMenu(Menu $menu)
    {
        return [
            'id' => $menu->id,
            'code' => $menu->code,
            'icon' => $menu->icon,
            'route' => $menu->route,
            'label' => optional($menu->translations->first())->label,
            'children' => $menu->children->map(
                fn($child) => self::mapMenu($child)
            )->values(),
        ];
    }
}
