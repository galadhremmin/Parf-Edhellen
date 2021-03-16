<?php

namespace App\Repositories\SearchIndexResolvers;

use App\Repositories\ValueObjects\SearchIndexSearchValue;
use App\Models\SearchKeyword;

abstract class SearchIndexResolverBase implements ISearchIndexResolver
{
    public function resolve(SearchIndexSearchValue $value): array
    {
        $query = $this->buildQuery($value);
        return $this->resolveByQuery($query, $value);
    }

    abstract protected function resolveByQuery(array $params, SearchIndexSearchValue $value): array;

    private function buildQuery(SearchIndexSearchValue $v): array
    {
        $word = $this->formatWord($v->getWord());
        $searchColumn = $v->getReversed() ? 'normalized_keyword_reversed_unaccented' : 'normalized_keyword_unaccented';
        $lengthColumn = $searchColumn.'_length';

        $query = SearchKeyword::where($searchColumn, 'like', $word);

        if ($v->getLanguageId() !== 0) {
            $query = $query->where('language_id', intval($v->getLanguageId()));
        }

        if ($v->getIncludesOld() === false) {
            $query = $query->where('is_old', 0);
        }
    
        if (! empty($v->getSpeechIds())) {
            $query = $query->whereIn('speech_id', $v->getSpeechIds());
        }

        if (! empty($v->getGlossGroupIds())) {
            $query = $query->whereIn('gloss_group_id', $v->getGlossGroupIds());
        }

        return [
            'query' => $query,
            'search_column' => $searchColumn,
            'length_column' => $lengthColumn
        ];
    }

    private function formatWord(string $word) 
    {
        if (strpos($word, '*') !== false) {
            return str_replace('*', '%', $word);
        } 

        return $word.'%';
    }
}
