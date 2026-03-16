<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiIntegration extends Model
{
    protected $table = 'm_api_integrations';
    protected $fillable = [
        'name',
        'prefix',
        'description',
        'is_active'
    ];

    public function apiTransactions()
    {
        return $this->hasMany(TrnSinkron::class);
    }

    public function lastTransaction()
    {
        return $this->hasOne(TrnSinkron::class)->latestOfMany();
    }
}
