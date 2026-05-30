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
        Schema::create('trn_pendaftaran_kartu_ujis', function (Blueprint $table) {
            $table->foreignId('pendaftaran_id')
                ->primary()
                ->constrained('trn_pendaftarans')
                ->cascadeOnDelete();
            $table->string('no_seri_kartu')->nullable()->unique();
            $table->date('tanggal_cetak')->default(DB::raw('CURRENT_DATE'));
            $table->foreignId('petugas_id')
                ->constrained('m_users')
                ->restrictOnDelete()->nullable();
            $table->string('petugas_nama')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trn_pendaftaran_kartu_ujis');
    }
};
