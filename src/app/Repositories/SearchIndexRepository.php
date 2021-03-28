<?php

namespace App\Repositories;

use App\Helpers\StringHelper;
use App\Models\Initialization\Morphs;
use App\Repositories\SearchIndexResolvers\{
    GlossSearchIndexResolver,
    KeywordsSearchIndexResolver
};
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
    private static $_resolvers = null;
    private $_keywordsResolver;

    public function __construct(KeywordsSearchIndexResolver $keywordsResolver)
    {
        $this->_keywordsResolver = $keywordsResolver;
    }

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
        $keywords = $this->_keywordsResolver->resolve($v);
        return $keywords;
    }

    public function resolveIndexToEntities(int $searchGroupId, SearchIndexSearchValue $v)
    {
        $entityName = $this->getEntityNameFromSearchGroup($searchGroupId);

        $config = config('ed.book_entities');
        $resolverName = $config[$entityName]['resolver'];
        $entities = resolve($resolverName)->resolve($v);

        $single = count($entities) === 1;
        $word   = $v->getWord();

        // DEPRECATED START: Backwards compatibility for the BookAdapter for the glossary
        if (array_key_exists('single', $entities)) {
            $single = $entities['single'];
            unset($entities['single']);
        }

        if (array_key_exists('word', $entities)) {
            $word = $entities['word'];
            unset($entities['word']);
        }
        // DEPRECATED END

        return [
            'entities'   => $entities,
            'group_id'   => $searchGroupId,
            'group_name' => Morphs::getAlias($entityName),
            'single'     => $single,
            'word'       => $word
        ];
    }

    private function getSearchGroup(string $entityName): int
    {
        $morpedModel = Morphs::getMorphedModel($entityName);
        $config = config('ed.book_entities');
        if (isset($config[$morpedModel])) {
            return $config[$morpedModel]['group_id'];
        }

        throw new \Exception(sprintf('Unrecognised search group for %s and %s.', $entityName, $morpedModel));
    }

    private function getEntityNameFromSearchGroup(int $searchGroupId): ?string
    {
        $config = config('ed.book_group_id_to_book_entities');
        if (isset($config[$searchGroupId])) {
            return $config[$searchGroupId];
        }

        throw new \Exception(sprintf('Unrecognised search group %d.', $searchGroupId));
    }
}
