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
        Schema::create('m_role_menus', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            // $table->foreignId('menu_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->references('id')->on('m_roles')->cascadeOnDelete();
            $table->foreignId('menu_id')->references('id')->on('m_menus')->cascadeOnDelete();

            $table->boolean('can_view')->default(true);
            $table->boolean('can_create')->default(false);
            $table->boolean('can_update')->default(false);
            $table->boolean('can_delete')->default(false);

            $table->unique(['role_id', 'menu_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_role_menus');
    }
};
