<?php

namespace App\Repositories;

use App\Helpers\StringHelper;
use App\Models\{ 
    Keyword,
    Gloss, 
    Sense, 
    SentenceFragment,
    Word
};

class KeywordRepository 
{
    public function createKeyword(Word $word, Sense $sense, Gloss $gloss = null,
        SentenceFragment $inflection = null)
    {
        $keyword = new Keyword;

        $keyword->keyword  = $word->word;
        $keyword->word_id  = $word->id;
        $keyword->sense_id = $sense->id;

        // Normalized keywords are primarily used for direct references, where accents do matter. A direct reference
        // can be _miiir_ which would match _mîr_ according to the default normalization scheme. See StringHelper for more
        // information.
        $keyword->normalized_keyword                            = $word->normalized_word;
        $keyword->normalized_keyword_length                     = mb_strlen($word->normalized_word);
        $keyword->reversed_normalized_keyword                   = $word->reversed_normalized_word;
        $keyword->reversed_normalized_keyword_length            = mb_strlen($word->reversed_normalized_word);

        // Unaccented keywords' columns are used for searching, because _mir_ should find _mir_, _mír_, _mîr_ etc.
        $normalizedUnaccented = StringHelper::normalize($word->word, false);
        $keyword->normalized_keyword_unaccented                 = $normalizedUnaccented;
        $keyword->normalized_keyword_unaccented_length          = mb_strlen($keyword->normalized_keyword_unaccented);
        $keyword->reversed_normalized_keyword_unaccented        = strrev($normalizedUnaccented);
        $keyword->reversed_normalized_keyword_unaccented_length = mb_strlen($keyword->reversed_normalized_keyword_unaccented);

        if ($gloss) {
            $keyword->gloss_id = $gloss->id;
            $keyword->is_sense = 0;
            $keyword->is_old = $gloss->gloss_group_id 
                ? $gloss->gloss_group->is_old
                : false;
            
            if ($inflection) {
                $keyword->sentence_fragment_id = $inflection->id;
            }

        } else {
            $keyword->is_sense = 1;
        }

        $keyword->save();
    }
}