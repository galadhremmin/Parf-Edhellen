<?php

namespace App\Repositories;

use App\Helpers\StringHelper;
use App\Models\Initialization\Morphs;
use App\Models\{
    Gloss,
    ForumPost,
    ModelBase,
    SearchKeyword,
    SentenceFragment,
    Word
};


class SearchIndexRepository 
{
    public function __construct()
    {

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

    private function getSearchGroup(string $entityName): int
    {
        $morpedModel = Morphs::getMorphedModel($entityName);
        if ($morpedModel === Gloss::class) {
            return SearchKeyword::SEARCH_GROUP_DICTIONARY;
        }

        if ($morpedModel === ForumPost::class) {
            return SearchKeyword::SEARCH_GROUP_FORUM_POST;
        }

        if ($morpedModel === SentenceFragment::class) {
            return SearchKeyword::SEARCH_GROUP_SENTENCE;
        }

        throw new \Exception(sprintf('Unrecognised search group for %s and %s.', $entityName, $morpedModel));
    }
}
