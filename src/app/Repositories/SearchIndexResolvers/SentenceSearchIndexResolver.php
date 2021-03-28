<?php

namespace App\Repositories\SearchIndexResolvers;

use App\Repositories\ValueObjects\SearchIndexSearchValue;
use App\Models\{
    Sentence,
    SentenceFragment
};

class SentenceSearchIndexResolver extends SearchIndexResolverBase
{
    public function resolveByQuery(array $params, SearchIndexSearchValue $value): array
    {
        $entityIds = $params['query'] //
            ->select('entity_id')
            ->where('entity_name', 'fragment')
            ->orderBy($params['search_column'], 'asc')
            ->limit(100)
            ->pluck('entity_id');

        $sentenceIds = SentenceFragment::whereIn('id', $entityIds)
            ->select('sentence_id')
            ->pluck('sentence_id');
        
        $sentences = Sentence::whereIn('id', $sentenceIds)
            ->get()
            ->toArray();

        return $sentences;
    }
}
