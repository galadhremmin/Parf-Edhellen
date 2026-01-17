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

    public function emptyResponse(): array
    {
        return [];
    }


    private function buildQuery(SearchIndexSearchValue $v): array
    {
        $word = $v->getWord();
        $searchColumn = $v->getReversed() ? 'normalized_keyword_reversed_unaccented' : 'normalized_keyword_unaccented';
        $lengthColumn = $searchColumn.'_length';

        // Use MATCH() AGAINST() with Boolean mode for better performance with fulltext indexes
        // The * operator provides prefix matching similar to LIKE 'term%' but much faster
        if ($v->getNaturalLanguage()) {
            $normalizedWord = StringHelper::normalize($word, /* accentsMatter = */ false, /* retainWildcard = */ false);
            $query = SearchKeyword::whereRaw('MATCH(' . $searchColumn . ') AGAINST(? IN NATURAL LANGUAGE MODE)', [$normalizedWord]);
        } else {
            // Use FULLTEXT BOOLEAN MODE for prefix matching - much faster than LIKE queries
            $normalizedWord = StringHelper::normalize($word, /* accentsMatter = */ false, /* retainWildcard = */ true);
            $fulltextTerm = StringHelper::prepareFulltextBooleanTerm($normalizedWord);
            
            $query = SearchKeyword::whereRaw('MATCH(' . $searchColumn . ') AGAINST(? IN BOOLEAN MODE)', [$fulltextTerm]);
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
}
