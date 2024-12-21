<?php

namespace App\Repositories;

use App\Helpers\StringHelper;
use App\Models\Word;

class WordRepository
{
    public function save(string $wordString, int $accountId): Word
    {
        $wordString = mb_strtolower(trim($wordString), 'utf-8');
        $word = Word::whereRaw('BINARY word = ?', [$wordString])->first();

        if (! $word) {
            $normalizedWordString = StringHelper::normalize($wordString);

            $word = new Word;
            $word->word = $wordString;
            $word->normalized_word = $normalizedWordString;
            $word->reversed_normalized_word = strrev($normalizedWordString);
            $word->account_id = $accountId;

            $word->save();
        }

        return $word;
    }
}
