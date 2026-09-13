<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Sentence fragment inflections used to live in sentence_fragment_inflection_rels, but the
        // live read path (SentenceRepository::getSentence) only eager-loads lexical_entry_inflections.
        // Everything left behind in the old table is therefore invisible on the phrase pages:
        // Gilraen's Linnod, for one, has ten inflections there and two here, so "Ónen" renders
        // without its past tense and "chebin" without its soft mutation. Copy the rows across.
        //
        // Inserted directly rather than through LexicalEntryInflectionRepository, which would fire
        // LexicalEntryInflectionsCreated and queue a search index job per row. The index doesn't
        // need them -- SentenceIndexerSubscriber indexes word fragments independently of their
        // inflections.
        $now = now();

        $legacy = DB::table('sentence_fragment_inflection_rels as r')
            ->join('sentence_fragments as f', 'f.id', '=', 'r.sentence_fragment_id')
            ->join('sentences as s', 's.id', '=', 'f.sentence_id')
            ->whereNull('r.deleted_at')
            ->whereNull('f.deleted_at')
            // lexical_entry_inflections.lexical_entry_id is NOT NULL, and an unlinked fragment
            // is stored as 0 rather than null (see CanMapSentence::mapSentenceFragments).
            ->whereNotNull('f.lexical_entry_id')
            ->where('f.lexical_entry_id', '>', 0)
            ->orderBy('f.id')
            ->orderBy('r.id')
            ->get([
                'r.inflection_id',
                'f.id as sentence_fragment_id',
                'f.lexical_entry_id',
                'f.speech_id',
                'f.fragment',
                'f.sentence_id',
                's.language_id',
                's.account_id',
            ]);

        // Pairs already carried over by an edit through the contribution flow.
        $existing = DB::table('lexical_entry_inflections')
            ->whereNotNull('sentence_fragment_id')
            ->get(['sentence_fragment_id', 'inflection_id'])
            ->map(fn ($row) => $row->sentence_fragment_id.':'.$row->inflection_id)
            ->flip();

        $rows = [];
        foreach ($legacy->groupBy('sentence_fragment_id') as $inflections) {
            // Inflections belonging to one fragment form a single group, the way
            // LexicalEntryInflectionRepository::saveInflectionAsOneGroup writes them.
            $uuid = (string) Str::uuid();
            $order = 0;

            foreach ($inflections as $inflection) {
                if ($existing->has($inflection->sentence_fragment_id.':'.$inflection->inflection_id)) {
                    continue;
                }

                $rows[] = [
                    'inflection_group_uuid' => $uuid,
                    'lexical_entry_id' => $inflection->lexical_entry_id,
                    'language_id' => $inflection->language_id,
                    'inflection_id' => $inflection->inflection_id,
                    'speech_id' => $inflection->speech_id,
                    // One sentence carries the sentinel account_id 0, which no account row
                    // matches; lexical_entry_inflections.account_id is nullable and FK-checked.
                    'account_id' => $inflection->account_id ?: null,
                    'sentence_id' => $inflection->sentence_id,
                    'sentence_fragment_id' => $inflection->sentence_fragment_id,
                    'is_neologism' => 0,
                    'is_rejected' => 0,
                    'source' => null,
                    'word' => $inflection->fragment,
                    'order' => $order++,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('lexical_entry_inflections')->insert($chunk);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Nothing to undo: sentence_fragment_inflection_rels still holds the originals, so this
        // migration only ever duplicated data that already existed elsewhere.
    }
};
