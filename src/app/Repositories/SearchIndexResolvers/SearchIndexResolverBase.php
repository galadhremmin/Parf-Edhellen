<?php

namespace App\Repositories\SearchIndexResolvers;

use App\Helpers\StringHelper;
use App\Models\SearchKeyword;
use App\Repositories\ValueObjects\SearchIndexSearchValue;

abstract class SearchIndexResolverBase implements ISearchIndexResolver
{
    public function resolve(SearchIndexSearchValue $value): array
    {
        $query = $this->buildQuery($value);

        return $this->resolveByQuery($query, $value);
    }

    public function resolveId(int $entityId): array
    {
        throw new \Exception('Not supported.');
    }

    abstract protected function resolveByQuery(array $params, SearchIndexSearchValue $value): array;

    private function buildQuery(SearchIndexSearchValue $v): array
    {
        $word = $this->formatWord($v->getWord());
        $searchColumn = $v->getReversed() ? 'normalized_keyword_reversed_unaccented' : 'normalized_keyword_unaccented';
        $lengthColumn = $searchColumn.'_length';

        // Use MATCH() AGAINST() with Boolean mode for better performance with fulltext indexes
        // The * operator provides prefix matching similar to LIKE 'term%' but much faster
        if ($v->getNaturalLanguage()) {
            $query = SearchKeyword::whereRaw('MATCH(' . $searchColumn . ') AGAINST(? IN NATURAL LANGUAGE MODE)', [$word]);
        } else {
            $word = str_replace('*', '%', $word);
            $query = SearchKeyword::where($searchColumn, 'like', $word);
        }

        if ($v->getLanguageId()) {
            $query = $query->where('language_id', $v->getLanguageId());
        }

        if ($v->getIncludesOld() === false) {
            $query = $query->where('is_old', 0);
        }

        if (! empty($v->getSpeechIds())) {
            $query = $query->whereIn('speech_id', $v->getSpeechIds());
        }

        if (! empty($v->getLexicalEntryGroupIds())) {
            $query = $query->whereIn('lexical_entry_group_id', $v->getLexicalEntryGroupIds());
        }

        return [
            'query' => $query,
            'search_column' => $searchColumn,
            'length_column' => $lengthColumn,
        ];
    }

    private function formatWord(string $word)
    {
        // Use StringHelper::normalize with retainWildcard=true to preserve any wildcards the client provides
        $normalizedWord = StringHelper::normalize($word, /* accentsMatter = */ false, /* retainWildcard = */ true);
        
        // If no wildcard is present, add one for prefix matching (similar to LIKE 'term%')
        if (strpos($normalizedWord, '*') === false) {
            $normalizedWord .= '*';
        }
        
        return $normalizedWord;
    }
}
