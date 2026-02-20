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

    public function roleMenus()
    {
        return $this->hasMany(RoleMenu::class);
    }

    /**
     * Many-to-Many: Role -> Users
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'm_role_users')
            ->withTimestamps();
    }
}
