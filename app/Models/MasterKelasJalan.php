<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterKelasJalan extends Model
{
    protected $primaryKey = 'kelasjalan_id';
    public $incrementing = false;
    protected $keyType = 'int'; // atau string

    protected $fillable = [
        'kelasjalan_id',
        'kelasjalan_code',
        'kelasjalan_name',
        'kelasjalan_desc',
        'muatan_sumbu_terberat',
        'vehicle_length',
        'vehicle_height',
        'vehicle_width'
    ];
}
