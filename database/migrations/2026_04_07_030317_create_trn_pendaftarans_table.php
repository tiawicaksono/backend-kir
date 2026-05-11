<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('trn_pendaftarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kendaraan_id')
                ->constrained('m_kendaraans')
                ->restrictOnDelete();
            $table->foreignId('status_penerbitan_id')
                ->references('issuance_id')
                ->on('master_status_penerbitans')
                ->restrictOnDelete();
            $table->foreignId('petugas_id')
                ->constrained('m_users')
                ->restrictOnDelete()->nullable();
            $table->string('petugas_nama')->nullable();
            $table->string('no_pendaftaran_harian');
            $table->string('no_pendaftaran_tahunan')->unique();
            $table->date('tanggal_pendaftaran')->default(DB::raw('CURRENT_DATE'));
            $table->date('tanggal_uji')->default(DB::raw('CURRENT_DATE'));
            $table->date('tanggal_mati_uji')->default(DB::raw('CURRENT_DATE'));
            $table->integer('lama_mati_uji')->default(0);
            $table->boolean('is_ganti_kartu')->default(false);
            $table->boolean('is_uji_ditempat')->default(false);
            $table->boolean('is_daftar_online')->default(false);
            $table->boolean('is_dikuasakan')->default(false);
            $table->foreignId('biro_jasa_id')->constrained('m_biro_jasas')->restrictOnDelete()->nullable();
            $table->string('nama_pengurus')->nullable();
            $table->string('company_pengurus')->nullable();
            $table->string('no_hp_pengurus')->nullable();
            $table->boolean('is_kartu_hilang')->default(false);
            $table->text('no_kartu_hilang')->nullable()->unique();
            $table->text('status')->default('belum datang');
            $table->unique([
                'kendaraan_id',
                'tanggal_uji',
                'status_penerbitan_id'
            ]);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trn_pendaftarans');
    }
};
