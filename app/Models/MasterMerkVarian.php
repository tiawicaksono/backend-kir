<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterMerkVarian extends Model
{
    protected $primaryKey = 'vehicle_varian_type_id';
    public $incrementing = false;
    protected $keyType = 'int'; // atau string

    protected $fillable = [
        'vehicle_varian_type_id',
        'vehicle_brand_id',
        'vehicle_varian_type_code',
        'vehicle_varian_type_name',
        'vehicle_varian_type_desc'
    ];

    public function merk()
    {
        return $this->belongsTo(MasterMerk::class, 'vehicle_brand_id', 'vehicle_brand_id');
    }

    public function tipe()
    {
        return $this->hasMany(MasterMerkVarianTipe::class, 'vehicle_varian_type_id', 'vehicle_varian_type_id');
    }
}
