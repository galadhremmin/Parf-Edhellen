<?php

namespace App\Repositories;

use App\Helpers\StringHelper;
use App\Models\Keyword;
use App\Models\Language;
use App\Models\LexicalEntry;
use App\Models\Sense;
use App\Models\Word;

class KeywordRepository
{
    public function createKeyword(Word $word, Sense $sense, ?LexicalEntry $lexicalEntry = null, ?Language $keywordLanguage = null, //
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

        if ($lexicalEntry !== null) {
            $data['lexical_entry_id'] = $lexicalEntry->id;
            $data['is_sense'] = 0;
            $data['is_old'] = $lexicalEntry->lexical_entry_group_id
                ? $lexicalEntry->lexical_entry_group->is_old
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

        $data['identity_hash'] = Keyword::identityHash($data);
        $updateFields = [
            'is_old', 'is_sense', 'normalized_keyword',
        ];
        Keyword::upsert([$data], ['identity_hash'], $updateFields);
    }
}
