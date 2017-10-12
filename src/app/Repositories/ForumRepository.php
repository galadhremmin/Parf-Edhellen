<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use App\Models\{ 
    Account, 
    Contribution, 
    ForumThread, 
    ForumPost, 
    ForumPostLike, 
    Translation, 
    Sentence 
};
use App\Models\Initialization\Morphs;

class ForumRepository
{
    public function getCommentCountForEntities(string $entityClassName, array $ids)
    {
        $morph = Morphs::getAlias($entityClassName);

        $result = ForumThread::where('entity_type', $morph)
            ->whereIn('entity_id', $ids)
            ->select('entity_id', DB::raw('count(*) as count'))
            ->groupBy('entity_id')
            ->get();

        $groupedResult = [];
        foreach ($result as $r) {
            $groupedResult[$r->entity_id] = $r->count;
        }

        return $groupedResult;
    }
}
