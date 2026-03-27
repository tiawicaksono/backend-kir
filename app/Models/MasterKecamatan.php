<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterKecamatan extends Model
{
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
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
}
