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
        Schema::create('m_kendaraan_spesifikasis', function (Blueprint $table) {
            $table->foreignId('kendaraan_id')
                ->primary()
                ->constrained('m_kendaraans')
                ->cascadeOnDelete();
            $table->integer('isi_silinder')->unsigned()->default(0);
            $table->decimal('daya_motor', 10, 2)->unsigned()->default(0);
            $table->foreignId('bahan_bakar_id')
                ->references('fuel_id')
                ->on('master_bahan_bakars')
                ->restrictOnDelete();
            $table->integer('panjang_utama')->unsigned()->default(0);
            $table->integer('lebar_utama')->unsigned()->default(0);
            $table->integer('tinggi_utama')->unsigned()->default(0);
            $table->integer('panjang_bak')->unsigned()->default(0);
            $table->integer('lebar_bak')->unsigned()->default(0);
            $table->integer('tinggi_bak')->unsigned()->default(0);
            $table->integer('roh')->unsigned()->default(0);
            $table->integer('foh')->unsigned()->default(0);
            $table->integer('jarak_terendah')->unsigned()->default(0);
            $table->foreignId('konfigurasi_sumbu_id')
                ->constrained('master_konfigurasi_sumbus')
                ->restrictOnDelete();
            $table->integer('jarak_sumbu_1_2')->unsigned()->default(0);
            $table->integer('jarak_sumbu_2_3')->unsigned()->default(0);
            $table->integer('jarak_sumbu_3_4')->unsigned()->default(0);
            $table->integer('jarak_sumbu_4_5')->unsigned()->default(0);
            $table->integer('berat_sumbu_1')->unsigned()->default(0);
            $table->integer('berat_sumbu_2')->unsigned()->default(0);
            $table->integer('berat_sumbu_3')->unsigned()->default(0);
            $table->integer('berat_sumbu_4')->unsigned()->default(0);
            $table->integer('berat_sumbu_5')->unsigned()->default(0);
            $table->string('pemakaian_sumbu_1')->nullable();
            $table->string('pemakaian_sumbu_2')->nullable();
            $table->string('pemakaian_sumbu_3')->nullable();
            $table->string('pemakaian_sumbu_4')->nullable();
            $table->integer('daya_dukung_sumbu_1')->unsigned()->default(0);
            $table->integer('daya_dukung_sumbu_2')->unsigned()->default(0);
            $table->integer('daya_dukung_sumbu_3')->unsigned()->default(0);
            $table->integer('daya_dukung_sumbu_4')->unsigned()->default(0);
            $table->integer('daya_dukung_sumbu_5')->unsigned()->default(0);
            $table->integer('jbb')->unsigned()->default(0);
            $table->integer('jbkb')->unsigned()->default(0);
            $table->integer('jbki')->unsigned()->default(0);
            $table->integer('mst')->unsigned()->default(0);
            $table->integer('daya_angkut_orang')->unsigned()->default(0);
            $table->integer('daya_angkut_barang')->unsigned()->default(0);
            $table->foreignId('kelas_jalan_id')
                ->references('kelasjalan_id')
                ->on('master_kelas_jalans')
                ->restrictOnDelete();
            $table->integer('ukuran_qr')->unsigned()->default(0);
            $table->integer('ukuran_p1')->unsigned()->default(0);
            $table->integer('ukuran_p2')->unsigned()->default(0);
            $table->integer('volume_tera')->unsigned()->default(0);
            $table->string('jenis_muatan')->nullable();
            $table->decimal('berat_jenis_muatan')->unsigned()->default(0);
            $table->decimal('volume_muatan')->unsigned()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_kendaraan_spesifikasis');
    }
};
