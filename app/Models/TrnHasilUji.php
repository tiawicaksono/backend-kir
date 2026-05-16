<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrnHasilUji extends Model
{
    protected $table = 'trn_hasil_ujis';

    // =========================
    // MASS ASSIGNMENT
    // =========================
    protected $fillable = [
        'pendaftaran_id',

        'is_datang',
        'tanggal_jam_datang',

        'is_cetak',
        'tanggal_jam_selesai',

        'is_lulus',

        'prauji',
        'is_lulus_prauji',
        'petugas_prauji',

        'emisi',
        'is_lulus_emisi',
        'petugas_emisi',

        'lampu',
        'is_lulus_lampu',
        'petugas_lampu',

        'pitlift',
        'is_lulus_pitlift',
        'petugas_pitlift',

        'rem',
        'is_lulus_rem',
        'petugas_rem',

        // EMISI
        'alatuji_emisi_co',
        'alatuji_emisi_hc',
        'alatuji_emisi_diesel',

        // LAMPU
        'alatuji_lampu_kuat_pancar_kanan',
        'alatuji_lampu_kuat_pancar_kiri',
        'alatuji_lampu_deviasi_kanan',
        'alatuji_lampu_deviasi_kiri',

        // REM
        'alatuji_gaya_pengereman1_kanan',
        'alatuji_gaya_pengereman2_kanan',
        'alatuji_gaya_pengereman3_kanan',
        'alatuji_gaya_pengereman4_kanan',

        'alatuji_gaya_pengereman1_kiri',
        'alatuji_gaya_pengereman2_kiri',
        'alatuji_gaya_pengereman3_kiri',
        'alatuji_gaya_pengereman4_kiri',

        'alatuji_total_gaya_pengereman',

        'alatuji_selisih_gaya_pengereman_roda_kiri_kanan_1',
        'alatuji_selisih_gaya_pengereman_roda_kiri_kanan_2',
        'alatuji_selisih_gaya_pengereman_roda_kiri_kanan_3',
        'alatuji_selisih_gaya_pengereman_roda_kiri_kanan_4',

        'alatuji_gaya_pengereman_parkir_kanan',
        'alatuji_gaya_pengereman_parkir_kiri',
        'alatuji_total_gaya_pengereman_parkir',

        // SAFETY
        'alatuji_alat_pemantul_cahaya_tambahan_kuning',
        'alatuji_alat_pemantul_cahaya_tambahan_putih',
        'alatuji_alat_pemantul_cahaya_tambahan_merah',

        'alatuji_kincup_roda_depan',
        'alatuji_tingkat_kebisingan',
        'alatuji_penunjuk_kecepatan',
        'alatuji_kedalaman_alur_ban',

        // FOTO
        'foto_depan',
        'foto_belakang',
        'foto_kanan',
        'foto_kiri',

        // PENGUJI
        'nama_penguji',
        'nip_penguji',
    ];

    // =========================
    // CASTING
    // =========================
    protected $casts = [
        'is_datang' => 'boolean',
        'is_cetak' => 'boolean',
        'is_lulus' => 'boolean',

        'prauji' => 'boolean',
        'is_lulus_prauji' => 'boolean',

        'emisi' => 'boolean',
        'is_lulus_emisi' => 'boolean',

        'lampu' => 'boolean',
        'is_lulus_lampu' => 'boolean',

        'pitlift' => 'boolean',
        'is_lulus_pitlift' => 'boolean',

        'rem' => 'boolean',
        'is_lulus_rem' => 'boolean',

        'tanggal_jam_datang' => 'datetime',
        'tanggal_jam_selesai' => 'datetime',

        'alatuji_emisi_co' => 'decimal:1',
        'alatuji_lampu_deviasi_kanan' => 'decimal:2',
        'alatuji_lampu_deviasi_kiri' => 'decimal:2',
    ];

    // =========================
    // RELATION
    // =========================
    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(TrnPendaftaran::class, 'pendaftaran_id');
    }

    // =========================
    // OPTIONAL HELPERS
    // =========================
    public function isLulusFinal(): bool
    {
        return (bool) $this->is_lulus;
    }
}
