<?php

namespace App\Repositories;

use App\Models\GlossInflection;
use Illuminate\Support\Collection;
use Ramsey\Uuid\Uuid;

class GlossInflectionRepository
{
    public function getInflectionsInGroups(Collection $groups)
    {
        return GlossInflection::whereIn('inflection_group_uuid', $groups) //
            ->with('inflection') //
            ->get() //
            ->groupBy('inflection_group_uuid');
    }

    public function getInflectionsForGloss(int $glossId)
    {
        return GlossInflection::where('gloss_id', $glossId)->get();
    }

    public function saveMany(Collection $inflectionGroups): array
    {
        $allInflections = [];
        $uuids = [];

        foreach ($inflectionGroups as $inflectionGroup) {
            $uuid = Uuid::uuid4();
            foreach ($inflectionGroup as $inflection) {
                $inflection->inflection_group_uuid = $uuid;
                $allInflections[] = $inflection;
            }
            $uuids[] = $uuid;
        }

        GlossInflection::insert($allInflections);
        return $uuids;
    }

    public function save(Collection $inflections): string
    {
        $uuid = Uuid::uuid4()->toString();
        $rows = [];
        foreach ($inflections as $inflection) {
            $rows[] = $inflection->toArray() + [
                'inflection_group_uuid' => $uuid
            ];
        }

        GlossInflection::insert($rows);
        return $uuid;
    }
}
