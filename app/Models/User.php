<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'm_users';
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function menus()
    {
        return $this->belongsToMany(
            Menu::class,
            'm_user_menus',   // pivot table
            'user_id',        // FK di pivot ke users
            'menu_id'         // FK di pivot ke menus
        );
    }

    /**
     * Many-to-Many: User -> Roles
     */
    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            'm_role_users',  // pivot table
            'user_id',
            'role_id'
        )->withPivot('is_active')->wherePivot('is_active', true);
    }

    public function directMenus()
    {
        return $this->belongsToMany(
            Menu::class,
            'm_user_menus',
            'user_id',
            'menu_id'
        );
    }

    public function getEffectiveMenus()
    {
        $roleIds = $this->roles()->pluck('m_roles.id');
        $userId = $this->id;

        // ambil menu yang boleh diakses role
        $menus = Menu::where('is_active', true)->whereHas('roles', function ($q) use ($roleIds) {
            $q->whereIn('m_roles.id', $roleIds);
        })
            ->whereNotIn('id', function ($query) use ($userId) {
                $query->select('menu_id')
                    ->from('m_user_menus')
                    ->where('user_id', $userId);
            })
            ->orderBy('order')
            ->distinct()
            ->get();

        // attach permission from m_role_menus
        return $this->buildTreeWithPermission($menus, $roleIds);
    }

    private function buildTreeWithPermission($menus, $roleIds)
    {
        $lookup = [];

        foreach ($menus as $menu) {
            // ambil permission dari pivot m_role_menus
            $perm = RoleMenu::where('menu_id', $menu->id)
                ->whereIn('role_id', $roleIds)
                ->first();

            $lookup[$menu->id] = (object) [
                'id'         => $menu->id,
                'code'       => $menu->code,
                'parent_id'  => $menu->parent_id,
                'icon'       => $menu->icon,
                'route'      => $menu->route,
                'order'      => $menu->order,
                'is_active'   => $menu->is_active,
                'can_view'    => $perm?->can_view ?? false,
                'can_create'  => $perm?->can_create ?? false,
                'can_update'  => $perm?->can_update ?? false,
                'can_delete'  => $perm?->can_delete ?? false,
                'children'    => [],
            ];
        }

        // build tree
        foreach ($lookup as $id => $menu) {
            if ($menu->parent_id !== null && isset($lookup[$menu->parent_id])) {
                $lookup[$menu->parent_id]->children[] = $menu;
            }
        }

        // return root only
        return array_values(array_filter($lookup, fn($m) => $m->parent_id === null));
    }
}
