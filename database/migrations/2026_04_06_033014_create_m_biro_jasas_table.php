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
        Schema::create('m_biro_jasas', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('company');
            $table->string('no_hp')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->unique(['no_hp']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_biro_jasas');
    }
};
