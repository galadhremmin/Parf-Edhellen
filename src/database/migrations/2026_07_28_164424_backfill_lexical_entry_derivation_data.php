<?php

use App\Jobs\RebuildLexicalEntryDerivationData;
use App\Models\LexicalEntryDerivationData;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // One-off backfill for entries imported before lexical_entry_derivation_data existed.
        // Same chunked, memory-bounded logic that runs quarterly after each Eldamo import — see
        // RebuildLexicalEntryDerivationData for why this is safe to run synchronously here.
        RebuildLexicalEntryDerivationData::dispatchSync();
    }

    public function down(): void
    {
        LexicalEntryDerivationData::query()->delete();
    }
};
