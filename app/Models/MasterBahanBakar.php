<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterBahanBakar extends Model
{
    protected $primaryKey = 'fuel_id';
    public $incrementing = false;
    protected $keyType = 'int'; // atau string

    protected $fillable = [
        'fuel_id',
        'fuel_name',
        'fuel_desc',
    ];
}
