<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterKecamatan extends Model
{
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $table = 'master_kecamatans';

    protected $fillable = [
        'id',
        'kota_id',
        'nama_kecamatan'
    ];

    public function kota()
    {
        return $this->belongsTo(MasterKota::class, 'kota_id', 'id');
    }

    public function kelurahans()
    {
        return $this->hasMany(MasterKelurahan::class, 'kelurahan_id', 'id');
    }

    public function kendaraans()
    {
        return $this->hasMany(MKendaraan::class, 'kecamatan_id', 'id');
    }

    public function pendaftaranRekomendasis()
    {
        return $this->hasMany(TrnPendaftaranRekomendasi::class, 'kecamatan_id', 'id');
    }
}
