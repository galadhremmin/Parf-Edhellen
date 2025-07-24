<?php

namespace App\Helpers;

use App\Models\Gloss;
use App\Models\LexicalEntryDetail;
use App\Models\Versioning\GlossVersion;
use Illuminate\Support\Collection;

class GlossAggregationHelper
{
    public function aggregate(array &$glosses)
    {
        $numberOfGlosses = count($glosses);
        if ($numberOfGlosses < 1) {
            return $numberOfGlosses;
        }

        $firstGloss = $glosses[0];
        if ($firstGloss instanceof Gloss ||
            $firstGloss instanceof GlossVersion) {
            return $this->aggregateEloquentEntities($glosses);
        }

        return $this->aggregateStdObjects($glosses);
    }

    private function aggregateEloquentEntities(array &$glosses)
    {
        // Gloss model entities already have the 'glosses' relation, and would therefore
        // only requiring eager loading. This is not performant to be doing in the adapter, and
        // therefore raises an error.
        $eagerLoading = ['glosses', 'lexical_entry_details'];

        foreach ($glosses as $gloss) {
            foreach ($eagerLoading as $relation) {
                if (! $gloss->relationLoaded($relation)) {
                    throw new \Exception('Failed to adapt gloss '.$gloss->id.' because its relation "'.
                        $relation.'" is not loaded.');
                }
            }
        }

        return count($glosses);
    }

    private function aggregateStdObjects(array &$glosses)
    {

        $collection = new Collection($glosses);
        $collection = $collection->groupBy('id');
        $collection = $collection->map(function ($items) {
            $gloss = $items[0];

            $gloss->glosses = $items->unique('translation')->map(function ($item) {
                return new Gloss(['translation' => $item->translation]);
            });
            unset($gloss->translation);

            $gloss->lexical_entry_details = $items->unique('lexical_entry_details_category')->map(function ($item) {
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
                $gloss->lexical_entry_details_category,
                $gloss->lexical_entry_details_order,
                $gloss->lexical_entry_details_text,
                $gloss->lexical_entry_details_type
            );

            return $gloss;
        });

        $glosses = $collection->values()->all();

        return count($glosses);
    }
}
