<?php

namespace App\Repositories;

use App\Helpers\StringHelper;
use App\Models\Initialization\Morphs;
use App\Repositories\ValueObjects\SearchIndexSearchValue;
use App\Models\{
    Gloss,
    ForumPost,
    ModelBase,
    SearchKeyword,
    SentenceFragment,
    Word
};

use DB;

class SearchIndexRepository 
{
    static $_searchGroupMappings = [
        Gloss::class            => SearchKeyword::SEARCH_GROUP_DICTIONARY,
        ForumPost::class        => SearchKeyword::SEARCH_GROUP_FORUM_POST,
        SentenceFragment::class => SearchKeyword::SEARCH_GROUP_SENTENCE
    ];

    public function createIndex(ModelBase $model, Word $word, string $inflection = null): SearchKeyword
    {
        $entityName   = Morphs::getAlias($model);
        $entityId     = $model->id;
        $keyword      = empty($inflection) ? $word->word : $inflection;
        $glossGroupId = null;

        $isOld = false;
        if ($model instanceOf Gloss) {
            $glossGroupId = $model->gloss_group_id;
            $isOld = $glossGroupId
                ? $model->gloss_group->is_old
                : false;
        }

        $normalizedKeyword           = StringHelper::normalize($keyword, true);
        $normalizedKeywordUnaccented = StringHelper::normalize($keyword, false);

        $normalizedKeywordReversed           = strrev($normalizedKeyword);
        $normalizedKeywordUnaccentedReversed = strrev($normalizedKeywordUnaccented);

        $data = [
            'keyword'                                => $keyword,
            'normalized_keyword'                     => $normalizedKeyword,
            'normalized_keyword_unaccented'          => $normalizedKeywordUnaccented,
            'normalized_keyword_reversed'            => $normalizedKeywordReversed,
            'normalized_keyword_reversed_unaccented' => $normalizedKeywordUnaccentedReversed,
            'keyword_length'                         => mb_strlen($keyword),
            'normalized_keyword_length'              => mb_strlen($normalizedKeyword),
            'normalized_keyword_unaccented_length'   => mb_strlen($normalizedKeywordUnaccented),
            'normalized_keyword_reversed_length'     => mb_strlen($normalizedKeywordUnaccented),
            'normalized_keyword_reversed_unaccented_length' => mb_strlen($normalizedKeywordUnaccentedReversed),

            'gloss_group_id' => $glossGroupId,
            'entity_name'    => $entityName,
            'entity_id'      => $entityId,
            'is_old'         => $isOld,
            'word'           => $word->word,
            'word_id'        => $word->id,

            'search_group'   => $this->getSearchGroup($entityName)
        ];

        $keyword = SearchKeyword::create($data);
        return $keyword;
    }

    public function deleteAll(ModelBase $model)
    {
        $entityName = Morphs::getAlias($model);
        if ($entityName === null) {
            return;
        }

        SearchKeyword::where([
            ['entity_name', $entityName],
            ['entity_id', $model->id]
        ])->delete();
    }

    public function findKeywords(SearchIndexSearchValue $v)
    {
        $queryData = $this->buildQuery($v);
        $keywords = $queryData['query'] //
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
            ->orderBy(DB::raw('MAX('.$queryData['length_column'].')'), 'asc')
            ->orderBy($queryData['search_column'], 'asc')
            ->limit(100)
            ->get();
        
        return $keywords;
    }

    public function resolveIndexToEntities(int $searchGroupId, SearchIndexSearchValue $v)
    {
        $queryData = $this->buildQuery($v);
        $entityIds = $queryData['query'] //
            ->select('entity_id')
            ->where('entity_name', $this->getEntityNameFromSearchGroup($searchGroupId))
            ->orderBy($queryData['search_column'], 'asc')
            ->limit(100)
            ->pluck('entity_id');

        return $entityIds;
    }

    private function buildQuery(SearchIndexSearchValue $v)
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

    private function formatWord(string $word, bool& $hasWildcard = null) 
    {
        if (strpos($word, '*') !== false) {
            $hasWildcard = true;
            return str_replace('*', '%', $word);
        } 
        
        $hasWildcard = false;
        return $word.'%';
    }

    private function getSearchGroup(string $entityName): int
    {
        $morpedModel = Morphs::getMorphedModel($entityName);
        if (isset(self::$_searchGroupMappings[$morpedModel])) {
            return self::$_searchGroupMappings[$morpedModel];
        }

        throw new \Exception(sprintf('Unrecognised search group for %s and %s.', $entityName, $morpedModel));
    }

    private function getEntityNameFromSearchGroup(int $searchGroupId): ?string
    {
        $mappings = array_flip(self::$_searchGroupMappings);

        if (isset($mappings[$searchGroupId])) {
            return Morphs::getAlias($mappings[$searchGroupId]);
        }

        throw new \Exception(sprintf('Unrecognised search group %d.', $searchGroupId));
    }
}
