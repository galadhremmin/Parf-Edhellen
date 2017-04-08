<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use App\Models\Translation;
use App\Models\Author;

class StatisticsRepository
{
    public function getStatisticsForAuthor(Author $author)
    {
        $noOfWords = DB::table('word')
            ->where('AuthorID', '=', $author->AccountID)
            ->count();

        $noOfTranslations = DB::table('translation')
            ->where([
                ['AuthorID', '=', $author->AccountID],
                ['Deleted',  '=', 0]
            ])
            ->count();

        $noOfSentences = 0;
        $noOfThanks = 0;

        return [
            'noOfWords'        => $noOfWords,
            'noOfTranslations' => $noOfTranslations,
            'noOfSentences'    => $noOfSentences,
            'noOfThanks'       => $noOfThanks
        ];
    }
}