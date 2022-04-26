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

    public function createIndex(ModelBase $model, Word $wordEntity, string $inflection = null): SearchKeyword
    {
        $entityName   = Morphs::getAlias($model);
        $entityId     = $model->id;
        $word         = StringHelper::toLower(StringHelper::clean($wordEntity->word));
        $inflection   = StringHelper::toLower(StringHelper::clean($inflection));
        $keyword      = empty($inflection) ? $word : $inflection;
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
            'word'           => $word,
            'word_id'        => $wordEntity->id,

            'search_group'   => $this->getSearchGroup($entityName)
        ];

        $keyword = SearchKeyword::create($data);
        $keyword->save();
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
        $intlName = $config[$entityName]['intl_name'];
        
        $resolver = resolve($resolverName);
        $entities = $resolver->resolve($v);

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

        $discussEntityType = $this->getDiscussEntityTypeFromEntityName($entityName);
        $entityMorph = Morphs::getAlias($discussEntityType);

        return [
            'entities'        => $entities,
            'group_id'        => $searchGroupId,
            'group_intl_name' => $intlName,
            'single'          => $single,
            'word'            => $word,
            'entity_morph'    => $entityMorph
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

    private function getDiscussEntityTypeFromEntityName(string $entityName): ?string
    {
        $config = config('ed.book_entities');
        if (isset($config[$entityName])) {
            return $config[$entityName]['discuss_entity_type'] ?: $entityName;
        }

        throw new \Exception(sprintf('Unrecognised entity name %s.', $entityName));
    }
}
