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
        Schema::create('master_kotas', function (Blueprint $table) {
            $table->id();
            $table->integer('provinsi_id');
            $table->foreign('provinsi_id')->references('id')->on('master_provinsis')->restrictOnDelete();
            $table->string('nama_kota');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_kotas');
    }
};
