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
        Schema::create('m_menu_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')
                ->references('id')->on('m_menus')
                ->cascadeOnDelete();
            $table->string('locale', 5); // id | en
            $table->string('name');
            $table->timestamps();

            $table->unique(['menu_id', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_menu_translations');
    }
};
