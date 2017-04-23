<?php

namespace App\Repositories;

use App\Models\Sentence;
use App\Models\Word;
use App\Models\Translation;
use App\Models\Author;

class StatisticsRepository
{
    public function getStatisticsForAuthor(Author $author)
    {
        $noOfWords = Word::where('AuthorID', '=', $author->AccountID)
            ->count();

        $noOfTranslations = Translation::notDeleted()
            ->where('AuthorID', $author->AccountID)
            ->count();

        $noOfSentences = Sentence::approved()
            ->where('AuthorID', $author->AccountID)
            ->count();

        $noOfThanks = 0;

        return [
            'noOfWords'        => $noOfWords,
            'noOfTranslations' => $noOfTranslations,
            'noOfSentences'    => $noOfSentences,
            'noOfThanks'       => $noOfThanks
        ];
    }
}