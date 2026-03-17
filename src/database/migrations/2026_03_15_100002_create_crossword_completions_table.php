<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crossword_completions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->unsignedBigInteger('crossword_puzzle_id');
            $table->unsignedSmallInteger('seconds_elapsed')->nullable();
            $table->boolean('is_assisted')->default(false);
            $table->timestamps();

            $table->unique(['account_id', 'crossword_puzzle_id'], 'crossword_completions_account_puzzle_unique');
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->foreign('crossword_puzzle_id')->references('id')->on('crossword_puzzles')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crossword_completions');
    }
};
