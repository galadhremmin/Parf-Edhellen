<?php

namespace App\Repositories;

use App\Events\LexicalEntryInflectionsCreated;
use App\Models\LexicalEntry;
use App\Models\LexicalEntryInflection;
use Illuminate\Support\Collection;
use Ramsey\Uuid\Uuid;

class LexicalEntryInflectionRepository
{
    public function getInflectionsForLexicalEntry(int $lexicalEntryId): Collection
    {
        return LexicalEntryInflection::where('lexical_entry_id', $lexicalEntryId)
            ->with('sentence', 'speech', 'inflection', 'language')
            ->get()
            ->groupBy('inflection_group_uuid');
    }

    public function getInflectionsForLexicalEntries(array $lexicalEntryIds): Collection
    {
        return LexicalEntryInflection::whereIn('lexical_entry_id', $lexicalEntryIds)
            ->with('sentence', 'speech', 'inflection', 'language')
            ->get()
            ->reduce(function ($inflections, $inflection) {
                // Group first by gloss ID
                if (! isset($inflections[$inflection->lexical_entry_id])) {
                    $inflections[$inflection->lexical_entry_id] = collect([]);
                }
                // ... and then by the inflection group UUID (which essentially creates inflection groups)
                if (! isset($inflections[$inflection->lexical_entry_id][$inflection->inflection_group_uuid])) {
                    $inflections[$inflection->lexical_entry_id][$inflection->inflection_group_uuid] = collect([]);
                }
                $inflections[$inflection->lexical_entry_id][$inflection->inflection_group_uuid]->push($inflection);

                return $inflections;
            }, collect([]));
    }

    public function saveManyOnLexicalEntry(LexicalEntry $lexicalEntry, Collection $inflections)
    {
        if ($inflections->count() < 1) {
            return;
        }

        $lexicalEntry->lexical_entry_inflections()->delete();
        $lexicalEntry->lexical_entry_inflections()->saveMany($inflections);

        event(new LexicalEntryInflectionsCreated($lexicalEntry, $inflections, /* incremental: */ false));
    }

    public function saveInflectionAsOneGroup(Collection $inflections): ?string
    {
        if ($inflections->count() < 1) {
            return null;
        }

        $uuid = Uuid::uuid4()->toString();
        $rows = [];
        foreach ($inflections as $inflection) {
            $rows[] = $inflection->toArray() + [
                'inflection_group_uuid' => $uuid,
            ];
        }

        LexicalEntryInflection::insert($rows);

        $lexicalEntry = LexicalEntry::findOrFail($inflections->first()->lexical_entry_id);
        event(new LexicalEntryInflectionsCreated($lexicalEntry, $inflections, /* incremental: */ true));

        return $uuid;
    }
}
