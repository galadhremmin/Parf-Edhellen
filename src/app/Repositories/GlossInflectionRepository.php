<?php

namespace App\Repositories;

use App\Events\{
    GlossInflectionsCreated
};
use App\Models\Gloss;
use App\Models\GlossInflection;
use Illuminate\Support\Collection;
use Ramsey\Uuid\Uuid;

class GlossInflectionRepository
{
    public function getInflectionsForGloss(int $glossId): Collection
    {
        return GlossInflection::where('gloss_id', $glossId)
            ->with('sentence', 'speech', 'inflection', 'language')
            ->get()
            ->groupBy('inflection_group_uuid');
    }

    public function getInflectionsForGlosses(array $glossIds): Collection
    {
        return GlossInflection::whereIn('gloss_id', $glossIds)
            ->with('sentence', 'speech', 'inflection', 'language')
            ->get()
            ->reduce(function ($inflections, $inflection) {
                // Group first by gloss ID
                if (! isset($inflections[$inflection->gloss_id])) {
                    $inflections[$inflection->gloss_id] = collect([]);
                }
                // ... and then by the inflection group UUID (which essentially creates inflection groups)
                if (! isset($inflections[$inflection->gloss_id][$inflection->inflection_group_uuid])) {
                    $inflections[$inflection->gloss_id][$inflection->inflection_group_uuid] = collect([]);
                }
                $inflections[$inflection->gloss_id][$inflection->inflection_group_uuid]->push($inflection);
                return $inflections;
            }, collect([]));
    }

    public function saveManyOnGloss(Gloss $gloss, Collection $inflections)
    {
        if ($inflections->count() < 1) {
            return;
        }

        $gloss->gloss_inflections()->delete();
        $gloss->gloss_inflections()->saveMany($inflections);

        event(new GlossInflectionsCreated($gloss, $inflections, /* incremental: */ false));
    }

    public function saveInflectionAsOneGroup(Collection $inflections): string
    {
        if ($inflections->count() < 1) {
            return null;
        }

        $uuid = Uuid::uuid4()->toString();
        $rows = [];
        foreach ($inflections as $inflection) {
            $rows[] = $inflection->toArray() + [
                'inflection_group_uuid' => $uuid
            ];
        }

        GlossInflection::insert($rows);

        $gloss = Gloss::findOrFail($inflections->first()->gloss_id);
        event(new GlossInflectionsCreated($gloss, $inflections, /* incremental: */ true));
        return $uuid;
    }
}
