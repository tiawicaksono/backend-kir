<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrnPendaftaran extends Model
{
    use SoftDeletes;

    protected $table = 'trn_pendaftarans';

    protected $fillable = [
        'kendaraan_id',
        'status_penerbitan_id',
        'no_pendaftaran',
        'tanggal_pendaftaran',
        'tanggal_uji',
        'tanggal_mati_uji',
        'lama_mati_uji',
        'is_ganti_kartu',
        'is_uji_ditempat',
        'is_daftar_online',
        'is_dikuasakan',
        'biro_jasa_id',
        'nama_pengurus',
        'company_pengurus',
        'no_hp_pengurus',
        'is_kartu_hilang',
        'no_kartu_hilang',
        'status',
    ];

    protected $casts = [
        'tanggal_pendaftaran' => 'date',
        'tanggal_uji' => 'date',
        'tanggal_mati_uji' => 'date',

        'is_ganti_kartu' => 'boolean',
        'is_uji_ditempat' => 'boolean',
        'is_daftar_online' => 'boolean',
        'is_dikuasakan' => 'boolean',
        'is_kartu_hilang' => 'boolean',

        'lama_mati_uji' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    // 🔗 ke kendaraan
    public function kendaraan()
    {
        return $this->belongsTo(MKendaraan::class, 'kendaraan_id', 'id');
    }

    // 🔗 ke status penerbitan
    public function statusPenerbitan()
    {
        return $this->belongsTo(MasterStatusPenerbitan::class, 'status_penerbitan_id', 'issuance_id');
    }

    // 🔗 ke biro jasa
    public function biroJasa()
    {
        return $this->belongsTo(MBiroJasa::class, 'biro_jasa_id', 'id');
    }
}
