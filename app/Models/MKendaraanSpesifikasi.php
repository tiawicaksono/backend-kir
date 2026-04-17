<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MKendaraanSpesifikasi extends Model
{
    use SoftDeletes;

    protected $table = 'm_kendaraan_spesifikasis';
    protected $primaryKey = 'kendaraan_id';
    protected $fillable = [
        'kendaraan_id',
        'isi_silinder',
        'daya_motor',
        'bahan_bakar_id',
        'panjang_utama',
        'lebar_utama',
        'tinggi_utama',
        'panjang_bak',
        'lebar_bak',
        'tinggi_bak',
        'roh',
        'foh',
        'jarak_terendah',
        'konfigurasi_sumbu_id',
        'jarak_sumbu_1_2',
        'jarak_sumbu_2_3',
        'jarak_sumbu_3_4',
        'jarak_sumbu_4_5',
        'berat_sumbu_1',
        'berat_sumbu_2',
        'berat_sumbu_3',
        'berat_sumbu_4',
        'berat_sumbu_5',
        'pemakaian_sumbu_1',
        'pemakaian_sumbu_2',
        'pemakaian_sumbu_3',
        'pemakaian_sumbu_4',
        'daya_dukung_sumbu_1',
        'daya_dukung_sumbu_2',
        'daya_dukung_sumbu_3',
        'daya_dukung_sumbu_4',
        'daya_dukung_sumbu_5',
        'jbb',
        'jbkb',
        'jbki',
        'mst',
        'daya_angkut_orang',
        'daya_angkut_barang',
        'kelas_jalan_id',
        'ukuran_qr',
        'ukuran_p1',
        'ukuran_p2',
        'volume_tera',
        'jenis_muatan',
        'berat_jenis_muatan',
        'volume_muatan',
    ];

    protected $numericDefaults  = [
        'isi_silinder',
        'daya_motor',
        'panjang_utama',
        'lebar_utama',
        'tinggi_utama',
        'panjang_bak',
        'lebar_bak',
        'tinggi_bak',
        'roh',
        'foh',
        'jarak_terendah',
        'jarak_sumbu_1_2',
        'jarak_sumbu_2_3',
        'jarak_sumbu_3_4',
        'jarak_sumbu_4_5',
        'berat_sumbu_1',
        'berat_sumbu_2',
        'berat_sumbu_3',
        'berat_sumbu_4',
        'berat_sumbu_5',
        'daya_dukung_sumbu_1',
        'daya_dukung_sumbu_2',
        'daya_dukung_sumbu_3',
        'daya_dukung_sumbu_4',
        'daya_dukung_sumbu_5',
        'jbb',
        'jbkb',
        'jbki',
        'mst',
        'daya_angkut_orang',
        'daya_angkut_barang',
        'ukuran_qr',
        'ukuran_p1',
        'ukuran_p2',
        'volume_tera',
        'berat_jenis_muatan',
        'volume_muatan',
    ];

    public function setAttribute($key, $value)
    {
        if (in_array($key, $this->numericDefaults)) {
            if ($value === '' || is_null($value)) {
                $value = 0;
            }
        }

        return parent::setAttribute($key, $value);
    }

    public function kendaraan()
    {
        return $this->belongsTo(MKendaraan::class, 'kendaraan_id', 'id');
    }

    public function bahanBakar()
    {
        return $this->belongsTo(MasterBahanBakar::class, 'bahan_bakar_id', 'fuel_id');
    }

    public function konfigurasiSumbu()
    {
        return $this->belongsTo(MasterKonfigurasiSumbu::class, 'konfigurasi_sumbu_id', 'id');
    }

    public function kelasJalan()
    {
        return $this->belongsTo(MasterKelasJalan::class, 'kelas_jalan_id', 'kelasjalan_id');
    }
}
