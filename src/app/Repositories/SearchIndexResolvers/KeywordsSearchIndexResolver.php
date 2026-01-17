<?php

namespace App\Repositories\SearchIndexResolvers;

use App\Repositories\ValueObjects\SearchIndexSearchValue;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class KeywordsSearchIndexResolver extends SearchIndexResolverBase
{
    protected function resolveByQuery(array $params, SearchIndexSearchValue $value): array
    {
        try {
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
            ->orderBy('search_group', 'asc')
            ->orderBy(DB::raw('MAX('.$params['length_column'].')'), 'asc')
            ->orderBy($params['search_column'], 'asc')
            ->limit(100)
            ->get()
            ->toArray();
        } catch (QueryException $_) {
            $keywords = [];
        }
        
        return $keywords;
    }
}
