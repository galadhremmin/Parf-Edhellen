<?php

namespace App\Repositories\SearchIndexResolvers;

use DB;
use App\Repositories\ValueObjects\SearchIndexSearchValue;
use App\Models\Gloss;
use App\Repositories\{
    DiscussRepository,
    GlossRepository,
    SentenceRepository
};
use App\Adapters\BookAdapter;

class KeywordsSearchIndexResolver extends SearchIndexResolverBase
{
    protected function resolveByQuery(array $params, SearchIndexSearchValue $value): array
    {
        $keywords = $params['query'] //
            ->select(
                'search_group as g',
                'keyword as k',
                'normalized_keyword as nk',
                'word as ok'
            )
            ->groupBy(
                'search_group',
                'keyword',
                'normalized_keyword',
                'word'
            )
            ->orderBy(DB::raw('MAX('.$params['length_column'].')'), 'asc')
            ->orderBy($params['search_column'], 'asc')
            ->limit(100)
            ->get()
            ->toArray();

        return $keywords;
    }
}
