<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterKelurahan extends Model
{
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $table = 'master_kelurahans';

    protected $fillable = [
        'id',
        'kecamatan_id',
        'nama_kelurahan'
    ];

    public function kecamatan()
    {
        return $this->belongsTo(MasterKecamatan::class, 'kecamatan_id', 'id');
    }

    public function kendaraans()
    {
        return $this->hasMany(MKendaraan::class, 'kelurahan_id', 'id');
    }

    public function pendaftaranRekomendasis()
    {
        return $this->hasMany(TrnPendaftaranRekomendasi::class, 'kelurahan_id', 'id');
    }
}
