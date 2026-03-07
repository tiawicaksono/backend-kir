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
        Schema::create('master_areas', function (Blueprint $table) {
            $table->integer('area_id')->primary();
            $table->string('area_code');
            $table->string('area_name');
            $table->string('area_address')->nullable();
            $table->string('area_email')->nullable();
            $table->string('area_pic')->nullable();
            $table->string('area_telp')->nullable();
            $table->boolean('area_active');
            $table->text('area_logo_active')->nullable();
            $table->text('logo')->nullable();
            $table->text('logo_gray')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_areas');
    }
};
