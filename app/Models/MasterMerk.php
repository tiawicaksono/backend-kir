<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterMerk extends Model
{
    protected $primaryKey = 'vehicle_brand_id';
    public $incrementing = false;
    protected $keyType = 'int'; // atau string

    protected $fillable = [
        'vehicle_brand_id',
        'vehicle_brand_code',
        'vehicle_brand_name',
        'vehicle_brand_desc',
    ];

    public function varians()
    {
        return $this->hasMany(MasterMerkVarian::class, 'vehicle_brand_id', 'vehicle_brand_id');
    }
}
