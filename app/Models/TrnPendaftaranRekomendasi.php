<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrnPendaftaranRekomendasi extends Model
{
    protected $table = 'trn_pendaftaran_rekomendasis';
    protected $primaryKey = 'pendaftaran_id';
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'pendaftaran_id',
        'no_surat_rekomendasi',
        'no_pemilik_tujuan',
        'nama_pemilik_tujuan',
        'alamat_pemilik_tujuan',
        'provinsi_id',
        'kota_id',
        'kecamatan_id',
        'kelurahan_id',
        'is_mutasi_keluar',
        'is_numpang_keluar',
        'area_asal_id',
        'area_tujuan_id',
        'status_sinkron',
        'keterangan_sinkron'
    ];

    protected $casts = [
        'pendaftaran_id' => 'integer',
        'no_surat_rekomendasi' => 'string',
        'no_pemilik_tujuan' => 'string',
        'nama_pemilik_tujuan' => 'string',
        'alamat_pemilik_tujuan' => 'string',
        'provinsi_id' => 'integer',
        'kota_id' => 'integer',
        'kecamatan_id' => 'integer',
        'kelurahan_id' => 'integer',
        'is_mutasi_keluar' => 'boolean',
        'is_numpang_keluar' => 'boolean',
        'area_asal_id' => 'integer',
        'area_tujuan_id' => 'integer',
        'status_sinkron' => 'boolean',
        'keterangan_sinkron' => 'string',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    // 🔗 ke pendaftaran
    public function pendaftaran()
    {
        return $this->belongsTo(TrnPendaftaran::class, 'pendaftaran_id');
    }
}
