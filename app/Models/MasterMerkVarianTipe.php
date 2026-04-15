<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterMerkVarianTipe extends Model
{
    protected $primaryKey = 'vehicle_varian_id';
    public $incrementing = false;
    protected $keyType = 'int'; // atau string

    protected $fillable = [
        'vehicle_varian_id',
        'vehicle_varian_type_id',
        'vehicle_varian_code',
        'vehicle_varian_name',
        'vehicle_varian_desc'
    ];

    public function varian()
    {
        return $this->belongsTo(MasterMerkVarian::class, 'vehicle_varian_type_id', 'vehicle_varian_type_id');
    }

    public function kendaraans()
    {
        return $this->hasMany(MKendaraan::class, 'tipe_varian_merk_id', 'vehicle_varian_id');
    }
}
