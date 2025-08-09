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
        Schema::table('search_keywords', function (Blueprint $table) {
            // Add individual fulltext indexes for each normalized keyword column
            // This allows precise control over which column is searched
            $table->fullText(['normalized_keyword']);
            $table->fullText(['normalized_keyword_reversed']);
            $table->fullText(['normalized_keyword_unaccented']);
            $table->fullText(['normalized_keyword_reversed_unaccented']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('search_keywords', function (Blueprint $table) {
            // Remove the individual fulltext indexes
            $table->dropFullText(['normalized_keyword']);
            $table->dropFullText(['normalized_keyword_reversed']);
            $table->dropFullText(['normalized_keyword_unaccented']);
            $table->dropFullText(['normalized_keyword_reversed_unaccented']);
        });
    }
};
