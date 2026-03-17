<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crossword_puzzles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('language_id');
            $table->date('puzzle_date');
            $table->json('grid')->nullable();
            $table->json('clues')->nullable();
            $table->timestamps();

            $table->unique(['language_id', 'puzzle_date'], 'crossword_puzzles_language_date_unique');
            $table->index('puzzle_date', 'crossword_puzzles_puzzle_date_index');
            $table->foreign('language_id')->references('id')->on('languages')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crossword_puzzles');
    }
};
