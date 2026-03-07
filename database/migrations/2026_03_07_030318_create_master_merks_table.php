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
        Schema::create('master_merks', function (Blueprint $table) {
            $table->integer('vehicle_brand_id')->primary();
            $table->string('vehicle_brand_code');
            $table->string('vehicle_brand_name');
            $table->string('vehicle_brand_desc')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_merks');
    }
};
