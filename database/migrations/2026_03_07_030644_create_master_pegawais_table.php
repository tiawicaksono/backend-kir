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
        Schema::create('master_pegawais', function (Blueprint $table) {
            $table->integer('job_type_id');
            $table->string('job_type_code');
            $table->string('job_type_name');
            $table->integer('job_id');
            $table->string('job_code');
            $table->string('job_name');
            $table->integer('user_id')->primary();
            $table->string('identity_number');
            $table->string('full_name');
            $table->string('pangkat')->nullable();
            $table->string('email')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('address')->nullable();
            $table->integer('sign_active');
            $table->text('sign1');
            $table->text('sign2');
            $table->text('sign3');
            $table->boolean('job_active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_pegawais');
    }
};
