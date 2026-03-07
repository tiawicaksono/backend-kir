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
        Schema::create('master_kelas_jalans', function (Blueprint $table) {
            $table->integer('kelasjalan_id')->primary();
            $table->string('kelasjalan_code');
            $table->string('kelasjalan_name');
            $table->string('kelasjalan_desc')->nullable();
            $table->integer('muatan_sumbu_terberat');
            $table->integer('vehicle_length');
            $table->integer('vehicle_height');
            $table->integer('vehicle_width');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_kelas_jalans');
    }
};
