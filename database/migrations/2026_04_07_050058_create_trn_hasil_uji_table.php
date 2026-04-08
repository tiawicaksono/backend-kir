<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('trn_hasil_ujis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pendaftaran_id')
                ->constrained('trn_pendaftarans')
                ->cascadeOnDelete();
            $table->boolean('is_datang')->default(false);
            $table->dateTime('tanggal_jam_datang')->nullable();
            $table->boolean('is_cetak')->default(false);
            $table->dateTime('tanggal_jam_selesai')->nullable();
            $table->boolean('is_lulus')->default(false);

            $table->boolean('prauji')->default(false);
            $table->boolean('is_lulus_prauji')->default(false);
            $table->string('petugas_prauji')->nullable();
            $table->boolean('emisi')->default(false);
            $table->boolean('is_lulus_emisi')->default(false);
            $table->string('petugas_emisi')->nullable();
            $table->boolean('lampu')->default(false);
            $table->boolean('is_lulus_lampu')->default(false);
            $table->string('petugas_lampu')->nullable();
            $table->boolean('pitlift')->default(false);
            $table->boolean('is_lulus_pitlift')->default(false);
            $table->string('petugas_pitlift')->nullable();
            $table->boolean('rem')->default(false);
            $table->boolean('is_lulus_rem')->default(false);


            $table->string('petugas_rem')->nullable();
            $table->decimal('alatuji_emisi_co', 2, 1)->nullable();
            $table->integer('alatuji_emisi_hc')->nullable();
            $table->integer('alatuji_emisi_diesel')->nullable();
            $table->integer('alatuji_lampu_kuat_pancar_kanan')->default(12000);
            $table->integer('alatuji_lampu_kuat_pancar_kiri')->default(12000);
            $table->decimal('alatuji_lampu_deviasi_kanan', 3, 2)->default(0);
            $table->decimal('alatuji_lampu_deviasi_kiri', 3, 2)->default(0);
            $table->integer('alatuji_gaya_pengereman1_kanan')->default(0);
            $table->integer('alatuji_gaya_pengereman2_kanan')->default(0);
            $table->integer('alatuji_gaya_pengereman3_kanan')->default(0);
            $table->integer('alatuji_gaya_pengereman4_kanan')->default(0);
            $table->integer('alatuji_gaya_pengereman1_kiri')->default(0);
            $table->integer('alatuji_gaya_pengereman2_kiri')->default(0);
            $table->integer('alatuji_gaya_pengereman3_kiri')->default(0);
            $table->integer('alatuji_gaya_pengereman4_kiri')->default(0);
            $table->integer('alatuji_total_gaya_pengereman')->default(0);
            $table->integer('alatuji_selisih_gaya_pengereman_roda_kiri_kanan_1')->default(0);
            $table->integer('alatuji_selisih_gaya_pengereman_roda_kiri_kanan_2')->default(0);
            $table->integer('alatuji_selisih_gaya_pengereman_roda_kiri_kanan_3')->default(0);
            $table->integer('alatuji_selisih_gaya_pengereman_roda_kiri_kanan_4')->default(0);
            $table->integer('alatuji_gaya_pengereman_parkir_kanan')->default(0);
            $table->integer('alatuji_gaya_pengereman_parkir_kiri')->default(0);
            $table->integer('alatuji_total_gaya_pengereman_parkir')->default(0);
            $table->integer('alatuji_alat_pemantul_cahaya_tambahan_kuning')->default(0);
            $table->integer('alatuji_alat_pemantul_cahaya_tambahan_putih')->default(0);
            $table->integer('alatuji_alat_pemantul_cahaya_tambahan_merah')->default(0);
            $table->integer('alatuji_kincup_roda_depan')->default(0);
            $table->integer('alatuji_tingkat_kebisingan')->default(0);
            $table->integer('alatuji_penunjuk_kecepatan')->default(0);
            $table->integer('alatuji_kedalaman_alur_ban')->default(0);
            $table->string('foto_depan')->nullable();
            $table->string('foto_belakang')->nullable();
            $table->string('foto_kanan')->nullable();
            $table->string('foto_kiri')->nullable();
            $table->string('nama_penguji')->nullable();
            $table->string('nip_penguji')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trn_hasil_ujis');
    }
};
