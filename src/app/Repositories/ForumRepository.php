<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use App\Models\{ 
    ForumThread, 
    ForumPost
};
use App\Models\Initialization\Morphs;

class ForumRepository
{
    public function getCommentCountForEntities(string $entityClassName, array $ids)
    {
        $morph = Morphs::getAlias($entityClassName);

        $result = ForumThread::where('entity_type', $morph)
            ->whereIn('entity_id', $ids)
            ->select('entity_id', 'number_of_posts')
            ->get();

        $groupedResult = [];
        foreach ($result as $r) {
            $groupedResult[$r->entity_id] = $r->number_of_posts;
        }

        return $groupedResult;
    }
}
