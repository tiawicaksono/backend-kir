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
        Schema::create('master_status_penerbitans', function (Blueprint $table) {
            $table->integer('issuance_id')->primary();
            $table->string('issuance_code');
            $table->string('issuance_name');
            $table->string('issuance_desc')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_status_penerbitans');
    }
};
