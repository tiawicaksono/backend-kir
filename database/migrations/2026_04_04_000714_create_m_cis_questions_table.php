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
        Schema::create('m_cis_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('m_cis_categories')->restrictOnDelete();
            $table->foreignId('sub_category_id')->nullable()->constrained('m_cis_sub_categories')->restrictOnDelete();

            $table->string('name');
            $table->enum('input_type', ['checkbox', 'select', 'number', 'text']);
            $table->string('unit')->nullable(); // %, ppm, dll
            $table->boolean('is_multiple')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_cis_questions');
    }
};
