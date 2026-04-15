<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterProvinsi extends Model
{
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'master_provinsis';

    protected $fillable = [
        'id',
        'nama_provinsi'
    ];

    public function kotas()
    {
        return $this->hasMany(MasterKota::class, 'provinsi_id', 'id');
    }

    public function kendaraans()
    {
        return $this->hasMany(MKendaraan::class, 'provinsi_id', 'id');
    }
}
