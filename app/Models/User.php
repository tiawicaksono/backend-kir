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
        // Ambil semua role_id aktif user
        $roleIds = $this->roles()->pluck('m_roles.id')->toArray();

        // Menu dari role
        $roleMenus = Menu::whereHas('roles', function ($q) use ($roleIds) {
            $q->whereIn('m_roles.id', $roleIds);
        })
            ->where('is_active', true)
            ->get()
            ->keyBy('id');

        // Ambil semua menu yang ada di m_user_menus untuk user ini (blacklist)
        $forbiddenMenuIds = UserMenu::where('user_id', $this->id)->pluck('menu_id')->toArray();

        // Hapus menu blacklist dari roleMenus
        foreach ($forbiddenMenuIds as $menuId) {
            unset($roleMenus[$menuId]);
        }

        return $roleMenus;
    }
}
