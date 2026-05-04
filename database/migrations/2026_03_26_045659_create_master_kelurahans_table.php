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
        Schema::create('master_kelurahans', function (Blueprint $table) {
            $table->id();
            $table->integer('kecamatan_id');
            $table->foreign('kecamatan_id')->references('id')->on('master_kecamatans')->restrictOnDelete();
            $table->string('nama_kelurahan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_kelurahans');
    }
};
