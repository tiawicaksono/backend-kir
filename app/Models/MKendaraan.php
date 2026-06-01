<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MKendaraan extends Model
{
    use SoftDeletes;

    protected $table = 'm_kendaraans';
    protected $primaryKey = 'id';
    protected $fillable = [
        'no_sut',
        'penerbit_sut',
        'tanggal_sut',
        'no_srut',
        'penerbit_srut',
        'tanggal_srut',
        'no_srb',
        'penerbit_srb',
        'tanggal_srb',
        'tanggal_stnk',
        'tahun_kendaraan',
        'no_uji',
        'no_kendaraan',
        'identitas',
        'no_identitas',
        'nama_pemilik',
        'no_hp',
        'alamat',
        'rt',
        'rw',
        'provinsi_id',
        'kota_id',
        'kecamatan_id',
        'kelurahan_id',
        'no_rangka',
        'no_mesin',
        'status',
        'merk_id',
        'varian_merk_id',
        'tipe_varian_merk_id',
        'jenis_kendaraan_id',
        'sub_jenis_kendaraan_id',
        'warna_cabin',
        'warna_bak',
        'bahan_utama_id',
        'jumlah_duduk',
        'jumlah_berdiri',
        'is_blokir',
        'alasan_blokir',
    ];

    protected static function booted()
    {
        static::deleting(function ($kendaraan) {

            $relation = $kendaraan->spesifikasiKendaraan();

            if ($kendaraan->isForceDeleting()) {
                // 🔥 force delete (hard delete)
                if ($relation->withTrashed()->exists()) {
                    $relation->withTrashed()->forceDelete();
                }
            } else {
                // 🔥 soft delete
                if ($relation->exists()) {
                    $relation->delete();
                }
            }
        });

        static::restoring(function ($kendaraan) {

            $relation = $kendaraan->spesifikasiKendaraan();

            if ($relation->withTrashed()->exists()) {
                $relation->withTrashed()->restore();
            }
        });
    }

    protected $normalizeFields = [
        // 'no_uji',
        'no_mesin',
        'no_rangka',
        'no_srb',
        'no_srut',
        'no_sut',
    ];

    protected $upperFields = [
        'nama_pemilik',
        'alamat',
        'no_rangka',
        'no_mesin',
        'status',
        'warna_cabin',
        'warna_bak',
    ];

    protected $numericDefaults = [
        'jumlah_duduk',
        'jumlah_berdiri'
    ];

    public function setAttribute($key, $value)
    {
        // 🔹 Normalize (hapus spasi + uppercase)
        if (in_array($key, $this->normalizeFields) && !is_null($value)) {
            $value = strtoupper(str_replace(' ', '', $value));
        }

        // 🔹 Uppercase
        if (in_array($key, $this->upperFields) && !is_null($value)) {
            $value = strtoupper($value);
        }

        // 🔹 Numeric default ("" / null → 0)
        if (in_array($key, $this->numericDefaults)) {
            if ($value === '' || is_null($value)) {
                $value = 0;
            }
        }

        return parent::setAttribute($key, $value);
    }

    public function spesifikasiKendaraan()
    {
        return $this->hasOne(MKendaraanSpesifikasi::class, 'kendaraan_id', 'id');
    }

    public function provinsi()
    {
        return $this->belongsTo(MasterProvinsi::class, 'provinsi_id');
    }

    public function kota()
    {
        return $this->belongsTo(MasterKota::class, 'kota_id');
    }

    public function kecamatan()
    {
        return $this->belongsTo(MasterKecamatan::class, 'kecamatan_id');
    }

    public function kelurahan()
    {
        return $this->belongsTo(MasterKelurahan::class, 'kelurahan_id');
    }

    public function merk()
    {
        return $this->belongsTo(MasterMerk::class, 'merk_id');
    }

    public function varianMerk()
    {
        return $this->belongsTo(MasterMerkVarian::class, 'varian_merk_id');
    }

    public function tipeVarianMerk()
    {
        return $this->belongsTo(MasterMerkVarianTipe::class, 'tipe_varian_merk_id');
    }

    public function jenisKendaraan()
    {
        return $this->belongsTo(MasterJenisKendaraan::class, 'jenis_kendaraan_id');
    }

    public function subJenisKendaraan()
    {
        return $this->belongsTo(MasterSubJenisKendaraan::class, 'sub_jenis_kendaraan_id');
    }

    public function bahanUtama()
    {
        return $this->belongsTo(MasterBahanUtama::class, 'bahan_utama_id');
    }
}
