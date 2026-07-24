<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Phonetic Developments are now sourced from the structured lexical_entry_phonetic_developments
        // table (see LexicalEntryPhoneticDevelopments.tsx) instead of the legacy free-text detail block.
        // Delete the latter for every entry that now has structured data, so the dictionary view
        // doesn't render the same content twice.
        DB::table('lexical_entry_details')
            ->where('category', 'Phonetic Developments')
            ->whereIn('lexical_entry_id', function ($query) {
                $query->select('lexical_entry_id')
                    ->from('lexical_entry_phonetic_developments')
                    ->distinct();
            })
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Point of no return — the deleted detail text isn't recoverable from the structured data.
    }
};
