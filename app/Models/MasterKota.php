<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterKota extends Model
{
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $table = 'master_kotas';

    protected $fillable = [
        'id',
        'provinsi_id',
        'nama_kota'
    ];

    public function provinsi()
    {
        return $this->belongsTo(MasterProvinsi::class, 'provinsi_id', 'id');
    }

    public function kecamatans()
    {
        return $this->hasMany(MasterKecamatan::class, 'kota_id', 'id');
    }

    public function kendaraans()
    {
        return $this->hasMany(MKendaraan::class, 'kota_id', 'id');
    }

    public function pendaftaranRekomendasis()
    {
        return $this->hasMany(TrnPendaftaranRekomendasi::class, 'kota_id', 'id');
    }
}
