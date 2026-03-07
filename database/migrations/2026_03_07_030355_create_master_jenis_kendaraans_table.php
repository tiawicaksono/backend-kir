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
        Schema::create('master_jenis_kendaraans', function (Blueprint $table) {
            $table->integer('vehicle_type_id')->primary();
            $table->string('vehicle_type_code');
            $table->string('vehicle_type_name');
            $table->string('vehicle_type_desc')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_jenis_kendaraans');
    }
};
