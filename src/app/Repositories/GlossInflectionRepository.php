<?php

namespace App\Repositories;

use App\Models\GlossInflection;
use Illuminate\Support\Collection;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class GlossInflectionRepository
{
    public function getInflectionsInGroups(Collection $groups)
    {
        return GlossInflection::whereIn('inflection_group_uuid', $groups) //
            ->with('inflection') //
            ->get() //
            ->groupBy('inflection_group_uuid');
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

    public function save(Collection $inflections): UuidInterface
    {
        $uuid = Uuid::uuid4();
        foreach ($inflections as $inflection) {
            $inflection->inflection_group_uuid = $uuid;
        }

        GlossInflection::insert($inflections);
        return $uuid;
    }
}
