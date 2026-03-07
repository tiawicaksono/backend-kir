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
        Schema::create('master_merk_varians', function (Blueprint $table) {
            $table->integer('vehicle_varian_type_id')->primary();
            $table->foreignId('vehicle_brand_id')->references('vehicle_brand_id')->on('master_merks')->cascadeOnDelete();
            $table->string('vehicle_varian_type_code');
            $table->string('vehicle_varian_type_name');
            $table->string('vehicle_varian_type_desc')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_merk_varians');
    }
};
