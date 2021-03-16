<?php

namespace App\Repositories\SearchIndexResolvers;

use App\Repositories\ValueObjects\SearchIndexSearchValue;
use App\Models\Gloss;
use App\Repositories\{
    DiscussRepository,
    GlossRepository,
    SentenceRepository
};
use App\Adapters\BookAdapter;

class SentenceSearchIndexResolver extends SearchIndexResolverBase
{
    public function resolveByQuery(array $params, SearchIndexSearchValue $value)
    {
        $entityIds = $params['query'] //
            ->select('entity_id')
            ->where('entity_name', 'sentence')
            ->orderBy($params['search_column'], 'asc')
            ->limit(100)
            ->pluck('entity_id');

        return Sentence::whereIn('id', $entityIds);
    }
}
