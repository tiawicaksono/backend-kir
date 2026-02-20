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
        Schema::create('m_menus', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();

            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('m_menus')
                ->cascadeOnDelete();

            $table->string('icon')->nullable();
            $table->string('route')->nullable();

            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_menus');
    }
};
