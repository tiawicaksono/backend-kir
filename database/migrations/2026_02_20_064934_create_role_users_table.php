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
        Schema::create('m_role_users', function (Blueprint $table) {
            $table->foreignId('user_id')->references('id')->on('m_users')->cascadeOnDelete();
            $table->foreignId('role_id')->references('id')->on('m_roles')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->integer('gedung_uji')->nullable();
            $table->boolean('prauji')->default(false);
            $table->boolean('emisi')->default(false);
            $table->boolean('lampu')->default(false);
            $table->boolean('pitlift')->default(false);
            $table->boolean('rem')->default(false);
            $table->timestamps();
            // composite primary key
            $table->primary(['user_id', 'role_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_role_users');
    }
};
