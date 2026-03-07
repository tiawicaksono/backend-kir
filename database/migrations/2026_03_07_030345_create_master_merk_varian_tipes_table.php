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
        Schema::create('master_merk_varian_tipes', function (Blueprint $table) {
            $table->integer('vehicle_varian_id')->primary();
            $table->foreignId('vehicle_varian_type_id')->references('vehicle_varian_type_id')->on('master_merk_varians')->cascadeOnDelete();
            $table->string('vehicle_varian_code');
            $table->string('vehicle_varian_name');
            $table->string('vehicle_varian_desc')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_merk_varian_tipes');
    }
};
