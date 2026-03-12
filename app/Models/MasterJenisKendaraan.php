<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterJenisKendaraan extends Model
{
    protected $primaryKey = 'vehicle_type_id';
    public $incrementing = false;
    protected $keyType = 'int'; // atau string

    protected $fillable = [
        'vehicle_type_id',
        'vehicle_type_code',
        'vehicle_type_name',
        'vehicle_type_desc',
    ];

    public function masterSubJenisKendaraans()
    {
        return $this->hasMany(MasterSubJenisKendaraan::class, 'vehicle_type_id', 'vehicle_type_id');
    }
}
