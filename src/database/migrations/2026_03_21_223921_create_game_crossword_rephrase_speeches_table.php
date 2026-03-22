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
        Schema::create('game_crossword_rephrase_speeches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('speech_id');
            $table->timestamps();

            $table->foreign('speech_id')->references('id')->on('speeches')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_crossword_rephrase_speeches');
    }
};
