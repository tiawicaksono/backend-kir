<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'm_roles';
    protected $fillable = [
        'name',
        'description',
    ];

    public function menus()
    {
        return $this->belongsToMany(
            Menu::class,
            'm_role_menus',
            'role_id',
            'menu_id'
        );
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'm_role_users')
            ->using(RoleUser::class)
            ->withPivot('is_active')
            ->withTimestamps();
    }
}
