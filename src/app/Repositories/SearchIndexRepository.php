<?php

namespace App\Repositories;

use App\Helpers\StringHelper;
use App\Models\Initialization\Morphs;
use App\Repositories\SearchIndexResolvers\{
    ISearchIndexResolver,
    KeywordsSearchIndexResolver
};
use App\Repositories\ValueObjects\SearchIndexSearchValue;
use App\Models\{
    Gloss,
    Language,
    ModelBase,
    SearchKeyword,
    Word
};
use App\Models\Interfaces\IHasLanguage;
use Illuminate\Support\Collection;

class SearchIndexRepository 
{
    private static $latestStoredIndexHashes = [];
    private static $upsetFields = ['keyword', 'language_id', 'gloss_group_id', 'entity_name', 'entity_id', 'is_old', 'word', 'word_id', 'search_group'];

    private $_keywordsResolver;
    private $_wordRepository;

    public function __construct(KeywordsSearchIndexResolver $keywordsResolver, WordRepository $wordRepository)
    {
        $this->_keywordsResolver = $keywordsResolver;
        $this->_wordRepository   = $wordRepository;
    }

    public function createIndex(ModelBase $model, Word $wordEntity, Language $keywordLanguage = null, string $inflection = null): void
    {
        $data = $this->saveIndexInternal($model, $wordEntity, $keywordLanguage, $inflection);

        if (config('ed.search_index_expands_english_infinitives')) {
            // Expansion of English infinitives is designed to create _two_ search keywords for every to-infinitive,
            // one with the to included and one without. It's a little bit slower and more expensive than the typcial
            // call.
            if (! $data['is_keyword_language_invented'] && $model instanceof Gloss) {
                $containsTo = preg_match('/^to\s\w{2,}/', $data['keyword']) === 1;
                $isVerb = (! $model->speech_id && $containsTo) ||
                    ($model->speech_id && $model->speech->is_verb);
                
                if ($isVerb) {
                    $expandedString = $containsTo ? substr($data['keyword'], 3 /* 'to ' */) : 'to '.$data['keyword'];
                    $expandedWord = $this->_wordRepository->save($expandedString, $model->account_id);
                    $this->saveIndexInternal($model, $expandedWord, $keywordLanguage);
                }
            }
        }
    }

    public function getForEntity(ModelBase $model)
    {
        $entityName = Morphs::getAlias($model);
        if ($entityName === null) {
            return;
        }

        return SearchKeyword::where([
            ['entity_name', $entityName],
            ['entity_id', $model->id]
        ])->get();
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

    public function deleteAllWithId(array $ids)
    {
        SearchKeyword::whereIn('id', $ids)->delete();
    }

    public function findKeywords(SearchIndexSearchValue $v)
    {
        $keywords = $this->_keywordsResolver->resolve($v);
        return $keywords;
    }

    public function resolveIndexToEntities(int $searchGroupId, SearchIndexSearchValue $v)
    {
        $resolver = $this->getResolverBySearchGroup($searchGroupId);
        $entities = $resolver->resolve($v);

        return $this->formatEntitiesResponse($entities, $searchGroupId, $v->getWord());
    }

    public function resolveEntity(int $searchGroupId, int $entityId)
    {
        $resolver = $this->getResolverBySearchGroup($searchGroupId);
        $entities = $resolver->resolveId($entityId);

        return $this->formatEntitiesResponse($entities, $searchGroupId);
    }

    public function indexSearch(array $terms, ?callable $filterFunc): Collection
    {
        $normalizedTerms = array_map(function ($term) {
            return StringHelper::normalize(
                StringHelper::toLower(StringHelper::clean($term)),
                true
            );
        }, $terms);

        $query = SearchKeyword::whereIn('normalized_keyword', $normalizedTerms);

        if (is_callable($filterFunc)) {
            $tmp = $filterFunc($query);
            if (! $tmp) {
                $query = $tmp;
            }
        }

        return $query
            ->get()
            ->groupBy('keyword');
    }

    private function saveIndexInternal(ModelBase $model, Word $wordEntity, Language $keywordLanguage = null, string $inflection = null): array
    {

        if (! $model->exists) {
            throw new \Exception("Search keyword target model does not exist. Make sure that the entity has been saved before calling this method.");
        }
        if (! $wordEntity->exists) {
            throw new \Exception("Word '".$wordEntity->word."' does not exist. Make sure that the entity has been saved before calling this method.");
        }

        $entityName   = Morphs::getAlias($model);
        $entityId     = $model->id;
        $word         = StringHelper::toLower(StringHelper::clean($wordEntity->word));
        $inflection   = StringHelper::toLower(StringHelper::clean($inflection));
        $keyword      = empty($inflection) ? $word : $inflection;
        $glossGroupId = null;
        $isOld        = false;
        
        if ($model instanceof Gloss) {
            $glossGroupId = $model->gloss_group_id;
            $isOld = $glossGroupId
                ? $model->gloss_group->is_old
                : false;
        }

        $languageId = null;
        if ($model instanceof IHasLanguage) {
            $languageId = $model->language_id;
        }

        $keywordLanguageId = null;
        $keywordLanguageIsInvented = true;
        if ($keywordLanguage !== null) {
            $keywordLanguageId = $keywordLanguage->id;
            $keywordLanguageIsInvented = $keywordLanguage->is_invented;
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

            'language_id'                  => $languageId,
            'keyword_language_id'          => $keywordLanguageId,
            'is_keyword_language_invented' => $keywordLanguageIsInvented,

            'gloss_group_id' => $glossGroupId,
            'entity_name'    => $entityName,
            'entity_id'      => $entityId,
            'is_old'         => $isOld,
            'word'           => $word,
            'word_id'        => $wordEntity->id,

            'search_group'   => $this->getSearchGroup($entityName)
        ];

        $hash = $this->makeStoreHash($data);
        if (! in_array($hash, self::$latestStoredIndexHashes)) {

            SearchKeyword::upsert([$data], self::$upsetFields, [
                // UPSERT update field if a row already exists
                'normalized_keyword', 'normalized_keyword_unaccented', 'normalized_keyword_reversed', 'normalized_keyword_reversed_unaccented',
                'keyword_length', 'normalized_keyword_length', 'normalized_keyword_unaccented_length', 'normalized_keyword_reversed_length',
                'normalized_keyword_reversed_unaccented_length', 'keyword_language_id', 'is_keyword_language_invented'
            ]);

            self::$latestStoredIndexHashes[] = $hash;
        }

        return $data;
    }

    public function getSearchGroup(string $entityName): int
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

    private function getResolverBySearchGroup(int $searchGroupId): ISearchIndexResolver
    {
        $entityName = $this->getEntityNameFromSearchGroup($searchGroupId);

        $config = config('ed.book_entities');
        if (! isset($config[$entityName])) {
            throw new \Exception($entityName.' is not a supported book entity.');
        }
        $resolverName = $config[$entityName]['resolver'];
        return resolve($resolverName);
    }

    private function getIntlByEntityName(string $entityName): string
    {
        $config = config('ed.book_entities');
        if (! isset($config[$entityName])) {
            throw new \Exception($entityName.' is not a supported book entity.');
        }
        return $config[$entityName]['intl_name'];
    }

    private function getDiscussEntityTypeFromEntityName(string $entityName): ?string
    {
        $config = config('ed.book_entities');
        if (isset($config[$entityName])) {
            return $config[$entityName]['discuss_entity_type'] ?: $entityName;
        }

        throw new \Exception(sprintf('Unrecognised entity name %s.', $entityName));
    }

    private function formatEntitiesResponse(array $entities, int $searchGroupId, ?string $word = null)
    {
        $entityName = $this->getEntityNameFromSearchGroup($searchGroupId);
        $single = count($entities) === 1;

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
        $intlName = $this->getIntlByEntityName($entityName);
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

    private function makeStoreHash(array $keywordData)
    {
        $values = '';
        
        $keys = array_keys($keywordData);
        sort($keys);

        foreach ($keys as $key) {
            $values .= $key.'='.$keywordData[$key].'|';
        }

        return sha1($values);
    }
}
