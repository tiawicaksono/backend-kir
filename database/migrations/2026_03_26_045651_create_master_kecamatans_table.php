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
        Schema::create('master_kecamatans', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('kota_id');
            $table->foreign('kota_id')->references('id')->on('master_kotas')->restrictOnDelete();
            $table->string('nama_kecamatan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_kecamatans');
    }
};
