<?php

namespace App\Repositories;

use App\Helpers\StringHelper;
use App\Models\Initialization\Morphs;
use App\Models\{
    Gloss,
    ModelBase,
    Word
};


class SearchIndexRepository 
{
    public function __construct()
    {

    }

    public function createIndex(ModelBase $model, Word $word, string $inflection = null)
    {
        $entityName = Morphs::getAlias($model);
        $entityId   = $model->id;
        $keyword    = empty($inflection) ? $word->word : $inflection;
        
        $isOld = false;
        if ($model instanceOf Gloss) {
            $isOld = $model->gloss_group_id 
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
            'entity_name'  => $entityName,
            'entity_id'    => $entityId,
            'is_old'       => $isOld,
            'word'         => $word->word,
            'word_id'      => $word->id,
        ];
    }
}