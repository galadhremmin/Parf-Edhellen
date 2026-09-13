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
        // Phrases imported from Eldamo need the same stable handle lexical entries already have,
        // so that re-running the import recognises what it created instead of duplicating it.
        Schema::table('sentences', function (Blueprint $table) {
            $table->string('external_id', 128)->nullable()->after('source');
            $table->index('external_id', 'ix_sentences_external_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sentences', function (Blueprint $table) {
            $table->dropIndex('ix_sentences_external_id');
            $table->dropColumn('external_id');
        });
    }
};
