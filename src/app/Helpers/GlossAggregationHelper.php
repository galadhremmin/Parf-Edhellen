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
        // Gloss model entities already have the 'translations' relation, and would therefore
        // only requiring eager loading. This is not performant to be doing in the adapter, and
        // therefore raises an error.
        $eagerLoading = ['translations', 'gloss_details'];

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

            $gloss->translations = $items->unique('translation')->reduce(function ($carry, $item) {
                $carry[] = new Gloss(['translation' => $item->translation]);

                return $carry;
            }, []);
            unset($gloss->translation);

            $gloss->gloss_details = $items->unique('gloss_details_category')->reduce(function ($carry, $item) {
                if ($item->gloss_details_category !== null) {
                    $carry[] = new LexicalEntryDetail([
                        'category' => $item->gloss_details_category,
                        'order' => $item->gloss_details_order,
                        'text' => $item->gloss_details_text,
                        'type' => $item->gloss_details_type,
                    ]);
                }

                return $carry;
            }, []);

            unset(
                $gloss->gloss_details_category,
                $gloss->gloss_details_order,
                $gloss->gloss_details_text,
                $gloss->gloss_details_type
            );

            return $gloss;
        });

        $glosses = $collection->values()->all();

        return count($glosses);
    }
}
