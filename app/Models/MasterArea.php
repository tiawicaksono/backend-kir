<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterArea extends Model
{
    protected $primaryKey = 'area_id';
    public $incrementing = false;
    protected $keyType = 'int'; // atau string

    protected $fillable = [
        'area_id',
        'area_code',
        'area_name',
        'area_address',
        'area_email',
        'area_pic',
        'area_telp',
        'area_active',
        'area_logo_active',
        'logo',
        'logo_gray',
    ];
}
