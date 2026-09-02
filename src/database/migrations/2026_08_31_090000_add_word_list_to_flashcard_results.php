<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Lets a flashcard result belong to a word list study session rather than to a language deck.
     */
    public function up(): void
    {
        // Three separate closures on purpose. MySQL commits DDL implicitly, so a failure part way
        // through a combined closure would leave the table half migrated with no way to roll back.
        Schema::table('flashcard_results', function (Blueprint $table) {
            $table->dropForeign('flashcard_results_flashcard_id_foreign');
        });

        Schema::table('flashcard_results', function (Blueprint $table) {
            $table->unsignedBigInteger('flashcard_id')->nullable()->change();
        });

        Schema::table('flashcard_results', function (Blueprint $table) {
            $table->foreign('flashcard_id')
                ->references('id')
                ->on('flashcards');

            // nullOnDelete rather than cascade: deleting a word list should not erase the study
            // history the user built up with it.
            $table->foreignId('word_list_id')
                ->nullable()
                ->after('flashcard_id')
                ->constrained('word_lists')
                ->nullOnDelete();

            $table->string('direction', 16)->default('forward')->after('correct');

            $table->index(['account_id', 'word_list_id']);
        });
    }

    public function down(): void
    {
        Schema::table('flashcard_results', function (Blueprint $table) {
            $table->dropIndex(['account_id', 'word_list_id']);
            $table->dropConstrainedForeignId('word_list_id');
            $table->dropColumn('direction');
        });

        Schema::table('flashcard_results', function (Blueprint $table) {
            $table->dropForeign('flashcard_results_flashcard_id_foreign');
        });

        // Rows created by word list study have no flashcard, so they must go before the column can
        // be made NOT NULL again.
        \Illuminate\Support\Facades\DB::table('flashcard_results')->whereNull('flashcard_id')->delete();

        Schema::table('flashcard_results', function (Blueprint $table) {
            $table->unsignedBigInteger('flashcard_id')->nullable(false)->change();
        });

        Schema::table('flashcard_results', function (Blueprint $table) {
            $table->foreign('flashcard_id')->references('id')->on('flashcards');
        });
    }
};
