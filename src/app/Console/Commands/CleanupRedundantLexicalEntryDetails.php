<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupRedundantLexicalEntryDetails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ed-import:delete-redundant-lexical-entry-details';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes lexical entry details (LexicalEntryDetail) which are no longer required since the information they contain is now natively supported by the data model. Examples of these are Phonetical Development, Derivations and Derivatives.';

    /**
     * Execute the console command.
     */
    public function handle()
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
}
