<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiIntegration extends Model
{
    protected $table = 'm_api_integrations';
    protected $fillable = [
        'name',
        'prefix',
        'is_active'
    ];
}
