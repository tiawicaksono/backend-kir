<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterSubJenisKendaraan extends Model
{
    protected $primaryKey = 'vehicle_sub_id';
    public $incrementing = false;
    protected $keyType = 'int'; // atau string

    protected $fillable = [
        'vehicle_sub_id',
        'vehicle_type_id',
        'vehicle_sub_code',
        'vehicle_sub_name',
        'vehicle_sub_desc',
    ];

    public function masterJenisKendaraan()
    {
        return $this->belongsTo(MasterJenisKendaraan::class, 'vehicle_type_id', 'vehicle_type_id');
    }
}
