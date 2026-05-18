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
        Schema::create('trn_pendaftaran_rekomendasis', function (Blueprint $table) {
            $table->foreignId('pendaftaran_id')
                ->primary()
                ->constrained('trn_pendaftarans')
                ->cascadeOnDelete();
            $table->string('no_surat_rekomendasi')->nullable()->unique();
            $table->string('no_pemilik_tujuan')->nullable();
            $table->string('nama_pemilik_tujuan')->nullable();
            $table->string('alamat_pemilik_tujuan')->nullable();

            $table->foreignId('provinsi_id')
                ->nullable()
                ->constrained('master_provinsis')
                ->restrictOnDelete();

            $table->foreignId('kota_id')
                ->nullable()
                ->constrained('master_kotas')
                ->restrictOnDelete();

            $table->foreignId('kecamatan_id')
                ->nullable()
                ->constrained('master_kecamatans')
                ->restrictOnDelete();

            $table->foreignId('kelurahan_id')
                ->nullable()
                ->constrained('master_kelurahans')
                ->restrictOnDelete();

            $table->boolean('is_mutasi_keluar')->default(false);
            $table->boolean('is_numpang_keluar')->default(false);
            $table->integer('area_tujuan_id')->nullable();
            $table->foreign('area_tujuan_id')->references('area_id')->on('master_areas')->restrictOnDelete();
            $table->boolean('status_sinkron')->nullable();
            $table->string('keterangan_sinkron')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trn_pendaftaran_rekomendasis');
    }
};
