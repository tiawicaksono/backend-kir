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
        Schema::create('trn_hasil_uji_tidak_luluses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hasil_uji_id')->constrained('trn_hasil_ujis')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('m_cis_questions')->cascadeOnDelete();

            $table->foreignId('option_id')->nullable()->constrained('m_cis_question_options')->nullOnDelete();

            $table->decimal('value_number', 10, 2)->nullable();
            $table->text('value_text')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trn_hasil_uji_tidak_luluses');
    }
};
