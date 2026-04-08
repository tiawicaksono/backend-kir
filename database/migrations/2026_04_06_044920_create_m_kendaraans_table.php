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
        Schema::create('m_kendaraans', function (Blueprint $table) {
            $table->id();
            $table->string('no_sut')->unique()->nullable();
            $table->string('penerbit_sut')->nullable();
            $table->date('tanggal_sut')->nullable();
            $table->string('no_srut')->unique()->nullable();
            $table->string('penerbit_srut')->nullable();
            $table->date('tanggal_srut')->nullable();
            $table->string('no_srb')->unique()->nullable();
            $table->string('penerbit_srb')->nullable();
            $table->date('tanggal_srb')->nullable();
            $table->date('tanggal_stnk')->nullable();
            $table->string('tahun_kendaraan')->nullable();
            $table->string('no_uji')->unique();
            $table->string('no_kendaraan');
            $table->string('identitas')->nullable();
            $table->string('no_identitas')->nullable();
            $table->string('nama_pemilik');
            $table->string('no_hp')->nullable();
            $table->string('alamat')->nullable();
            $table->string('rt')->nullable();
            $table->string('rw')->nullable();
            $table->string('provinsi_id');
            $table->foreign('provinsi_id')->references('id')->on('master_provinsis')->restrictOnDelete();
            $table->string('kota_id');
            $table->foreign('kota_id')->references('id')->on('master_kotas')->restrictOnDelete();
            $table->string('kecamatan_id');
            $table->foreign('kecamatan_id')->references('id')->on('master_kecamatans')->restrictOnDelete();
            $table->string('kelurahan_id');
            $table->foreign('kelurahan_id')->references('id')->on('master_kelurahans')->restrictOnDelete();
            $table->string('no_rangka')->unique();
            $table->string('no_mesin')->unique()->nullable();
            $table->string('status')->default('BUKAN UMUM');
            $table->foreignId('merk_id')
                ->references('vehicle_brand_id')
                ->on('master_merks')
                ->restrictOnDelete();
            $table->foreignId('varian_merk_id')
                ->references('vehicle_varian_type_id')
                ->on('master_merk_varians')
                ->restrictOnDelete();
            $table->foreignId('tipe_varian_merk_id')
                ->references('vehicle_varian_id')
                ->on('master_merk_varian_tipes')
                ->restrictOnDelete();
            $table->foreignId('jenis_kendaraan_id')
                ->references('vehicle_type_id')
                ->on('master_jenis_kendaraans')
                ->restrictOnDelete();
            $table->foreignId('sub_jenis_kendaraan_id')
                ->references('vehicle_sub_id')
                ->on('master_sub_jenis_kendaraans')
                ->restrictOnDelete();
            $table->string('warna_cabin')->nullable();
            $table->string('warna_bak')->nullable();
            $table->foreignId('bahan_utama_id')
                ->constrained('master_bahan_utamas')
                ->restrictOnDelete();
            $table->string('jenis_bahan_utama');
            $table->integer('jumlah_duduk')->default(0);
            $table->integer('jumlah_berdiri')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_kendaraans');
    }
};
