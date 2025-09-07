<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create word_lists table
        Schema::create('word_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('accounts')->onDelete('cascade');
            $table->string('name', 128);
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamps();
            
            $table->index('account_id', 'word_lists_account_id_index');
            $table->index(['account_id', 'name'], 'word_lists_account_name_index');
        });

        // Create word_list_entries pivot table
        Schema::create('word_list_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('word_list_id')->constrained('word_lists')->onDelete('cascade');
            $table->foreignId('lexical_entry_id')->constrained('lexical_entries')->onDelete('cascade');
            $table->integer('order')->nullable();
            $table->timestamps();
            
            $table->unique(['word_list_id', 'lexical_entry_id'], 'word_list_entries_unique');
            $table->index('word_list_id', 'word_list_entries_list_id_index');
            $table->index('lexical_entry_id', 'word_list_entries_lexical_entry_id_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('word_list_entries');
        Schema::dropIfExists('word_lists');
    }
};