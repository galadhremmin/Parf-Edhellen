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
        $searchColumn = 'normalized_keyword_unaccented';
        $lengthColumn = $searchColumn.'_length';

        $normalizedWord = StringHelper::transliterate($word, /* transformAccentsIntoLetters = */ false);

        if ($v->getNaturalLanguage()) {
            $fulltextTerm = str_replace('*', '', $normalizedWord);
            $query = SearchKeyword::whereRaw('MATCH(' . $searchColumn . ') AGAINST(? IN NATURAL LANGUAGE MODE)', [$fulltextTerm]);
        } else {

            // If the normalized word contains any Boolean Mode reserved symbols, don't add a wildcard.
            // Otherwise, add '*' to the end of the normalized word for prefix matching.
            if (preg_match('/[+\-<>\(\)~"@\*]/', $normalizedWord)) {
                $fulltextTerm = $normalizedWord;
            } else {
                $fulltextTerm = $normalizedWord.'*';
            }

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
