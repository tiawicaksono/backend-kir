<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;


class RoleUser extends Pivot
{
    protected $table = 'm_role_users';
    protected $fillable = [
        'user_id',
        'role_id',
        'gedung_uji',
        'prauji',
        'emisi',
        'lampu',
        'pitlift',
        'rem',
        'is_active'
    ];

    protected $casts = [
        'prauji' => 'boolean',
        'emisi' => 'boolean',
        'lampu' => 'boolean',
        'pitlift' => 'boolean',
        'rem' => 'boolean',
        'is_active' => 'boolean',
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
