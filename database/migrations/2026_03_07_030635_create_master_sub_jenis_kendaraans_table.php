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
        Schema::create('master_sub_jenis_kendaraans', function (Blueprint $table) {
            $table->integer('vehicle_sub_id')->primary();
            $table->foreignId('vehicle_type_id')->references('vehicle_type_id')->on('master_jenis_kendaraans')->cascadeOnDelete();
            $table->string('vehicle_sub_code');
            $table->string('vehicle_sub_name');
            $table->string('vehicle_sub_desc')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_sub_jenis_kendaraans');
    }
};
