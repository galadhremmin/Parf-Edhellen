<?php

namespace App\Helpers;

use App\Models\Gloss;
use App\Models\LexicalEntry;
use App\Models\LexicalEntryDetail;
use App\Models\Versioning\LexicalEntryVersion;
use Illuminate\Support\Collection;

class LexicalEntryAggregationHelper
{
    public function aggregate(array &$lexicalEntries)
    {
        $numberOfLexicalEntries = count($lexicalEntries);
        if ($numberOfLexicalEntries < 1) {
            return $numberOfLexicalEntries;
        }

        $firstLexicalEntry = $lexicalEntries[0];
        if ($firstLexicalEntry instanceof LexicalEntry ||
            $firstLexicalEntry instanceof LexicalEntryVersion) {
            return $this->aggregateEloquentEntities($lexicalEntries);
        }

        return $this->aggregateStdObjects($lexicalEntries);
    }

    private function aggregateEloquentEntities(array &$lexicalEntries)
    {
        // LexicalEntry model entities already have the 'glosses' relation, and would therefore
        // only requiring eager loading. This is not performant to be doing in the adapter, and
        // therefore raises an error.
        $eagerLoading = ['glosses', 'lexical_entry_details'];

        foreach ($lexicalEntries as $lexicalEntry) {
            foreach ($eagerLoading as $relation) {
                if (! $lexicalEntry->relationLoaded($relation)) {
                    throw new \Exception('Failed to adapt lexical entry '.$lexicalEntry->id.' because its relation "'.
                        $relation.'" is not loaded.');
                }
            }
        }

        return count($lexicalEntries);
    }

    private function aggregateStdObjects(array &$lexicalEntries)
    {
        $collection = new Collection($lexicalEntries);
        $collection = $collection->groupBy('id');
        $collection = $collection->map(function ($items) {
            $lexicalEntry = $items[0];

            $lexicalEntry->glosses = $items->unique('translation')->map(function ($item) {
                return new Gloss(['translation' => $item->translation]);
            });
            unset($lexicalEntry->translation);

            $lexicalEntry->lexical_entry_details = $items->unique('lexical_entry_details_category')->map(function ($item) {
                if ($item->lexical_entry_details_category !== null) {
                    return new LexicalEntryDetail([
                        'category' => $item->lexical_entry_details_category,
                        'order' => $item->lexical_entry_details_order,
                        'text' => $item->lexical_entry_details_text,
                        'type' => $item->lexical_entry_details_type,
                    ]);
                }

                return null;
            })->filter();

            unset(
                $lexicalEntry->lexical_entry_details_category,
                $lexicalEntry->lexical_entry_details_order,
                $lexicalEntry->lexical_entry_details_text,
                $lexicalEntry->lexical_entry_details_type
            );

            return $lexicalEntry;
        });

        $lexicalEntries = $collection->values()->all();

        return count($lexicalEntries);
    }
}
