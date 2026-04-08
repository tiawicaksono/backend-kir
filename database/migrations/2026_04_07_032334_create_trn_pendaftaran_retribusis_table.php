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
        Schema::create('trn_pendaftaran_retribusis', function (Blueprint $table) {
            $table->foreignId('pendaftaran_id')
                ->primary()
                ->constrained('trn_pendaftarans')
                ->cascadeOnDelete();
            $table->integer('b_daftar')->default(0);
            $table->integer('b_cetak')->default(0);
            $table->integer('b_denda')->default(0);
            $table->integer('jumlah_retribusi')->default(0);
            $table->boolean('status_pembayaran')->default(false);
            $table->text('virtual_account')->nullable()->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trn_pendaftaran_retribusis');
    }
};
