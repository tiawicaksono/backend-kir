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
        Schema::create('trn_hasil_uji_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hasil_uji_id')->constrained('trn_hasil_ujis')->cascadeOnDelete();
            $table->enum('field', [
                'prauji',
                'emisi',
                'lampu',
                'pitlift',
                'rem',
            ]);
            $table->boolean('is_lulus')->default(false);
            $table->string('petugas')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('finished_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trn_hasil_uji_logs');
    }
};
