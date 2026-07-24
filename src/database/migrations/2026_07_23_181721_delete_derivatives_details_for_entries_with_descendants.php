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
        // Derivatives (words derived FROM this entry) are now built from the structured
        // lexical_entry_derivations table (see BookAdapter::adaptDerivatives()) instead of the
        // legacy free-text detail block, for entries that have descendants. Delete the latter
        // for those entries so the single-entry view doesn't render the same content twice.
        DB::table('lexical_entry_details')
            ->where('category', 'Derivatives')
            ->whereIn('lexical_entry_id', function ($query) {
                $query->select('parent_lexical_entry_id')
                    ->from('lexical_entry_derivations')
                    ->whereNotNull('parent_lexical_entry_id')
                    ->distinct();
            })
            ->delete();

        // Same logic, the other direction: Derivations (what this entry comes FROM) are now
        // built from the same table (see BookAdapter::adaptDerivations(), LexicalEntryDerivations.tsx)
        // instead of the legacy free-text detail block, for entries that have their own ancestry
        // rows. Delete the latter for those entries for the same no-duplicate-rendering reason.
        DB::table('lexical_entry_details')
            ->where('category', 'Derivations')
            ->whereIn('lexical_entry_id', function ($query) {
                $query->select('lexical_entry_id')
                    ->from('lexical_entry_derivations')
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
