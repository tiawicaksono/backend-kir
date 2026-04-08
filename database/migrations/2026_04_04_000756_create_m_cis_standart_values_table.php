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
        Schema::create('m_cis_standart_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('m_cis_questions')->restrictOnDelete();

            $table->decimal('min_value', 10, 2)->nullable();
            $table->decimal('max_value', 10, 2)->nullable();

            $table->integer('tahun_min')->nullable();
            $table->integer('tahun_max')->nullable();

            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_cis_standart_values');
    }
};
