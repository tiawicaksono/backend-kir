<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiToken extends Model
{
    protected $table = 'm_api_tokens';
    protected $fillable = [
        'name',
        'token',
        'is_active'
    ];
}
