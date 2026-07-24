<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lexical_entry_featured_promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('accounts')->onDelete('cascade');
            $table->string('search_word', 191);
            $table->foreignId('language_id')->constrained('languages')->onDelete('cascade');
            $table->foreignId('lexical_entry_id')->constrained('lexical_entries')->onDelete('cascade');
            $table->foreignId('previous_lexical_entry_id')->nullable()
                ->constrained('lexical_entries', 'id', 'lexical_entry_featured_promotions_previous_entry_foreign')
                ->onDelete('set null');
            $table->timestamps();

            $table->index(['search_word', 'language_id'], 'lexical_entry_featured_promotions_word_lang_index');
            $table->index('account_id', 'lexical_entry_featured_promotions_account_id_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lexical_entry_featured_promotions');
    }
};
