<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;


class RoleUser extends Pivot
{
    protected $table = 'm_role_users';
    protected $fillable = [
        'user_id',
        'role_id',
        'is_active'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
