<?php

namespace App\Repositories;

use App\Helpers\StringHelper;
use App\Models\Gloss;
use App\Models\Keyword;
use App\Models\Language;
use App\Models\Sense;
use App\Models\Word;

class KeywordRepository
{
    public function createKeyword(Word $word, Sense $sense, ?Gloss $gloss = null, ?Language $keywordLanguage = null, //
        ?string $inflection = null, int $inflectionId = 0): void
    {
        $keywordString = $inflection ? $inflection : $word->word;

        // Normalized keywords are primarily used for direct references, where accents do matter. A direct reference
        // can be _miiir_ which would match _mîr_ according to the default normalization scheme. See StringHelper for more
        // information.
        $normalizedAccented = StringHelper::normalize($keywordString, true);

        $data = [
            'keyword' => $keywordString,
            'word' => $word->word,
            'word_id' => $word->id,
            'sense_id' => $sense->id,
            'normalized_keyword' => $normalizedAccented,
        ];

        if ($gloss !== null) {
            $data['gloss_id'] = $gloss->id;
            $data['is_sense'] = 0;
            $data['is_old'] = $gloss->gloss_group_id
                ? $gloss->gloss_group->is_old
                : false;

            if ($inflectionId) {
                $data['sentence_fragment_id'] = $inflectionId;
            }

        } else {
            $data['is_sense'] = 1;
        }

        if ($keywordLanguage !== null) {
            $data['keyword_language_id'] = $keywordLanguage->id;
        }

        $qualifyingFields = [
            'keyword', 'word_id', 'sense_id', 'gloss_id', 'sentence_fragment_id', 'keyword_language_id',
        ];
        $updateFields = [
            'is_old', 'is_sense', 'normalized_keyword',
        ];
        Keyword::upsert([$data], $qualifyingFields, $updateFields);
    }
}
