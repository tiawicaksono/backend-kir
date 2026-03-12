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
        Schema::create('trn_sinkrons', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('url_api');
            $table->text('token');
            $table->string('prefix');
            $table->boolean('status');
            $table->string('keterangan');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trn_sinkrons');
    }
};
