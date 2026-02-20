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
        Schema::create('m_user_menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->references('id')->on('m_users')->cascadeOnDelete();
            $table->foreignId('menu_id')->references('id')->on('m_menus')->cascadeOnDelete();

            $table->unique(['user_id', 'menu_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_user_menus');
    }
};
