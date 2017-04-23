<?php

namespace App\Repositories;

use App\Models\Sentence;
use App\Models\Word;
use App\Models\Translation;
use App\Models\Account;

class StatisticsRepository
{
    public function getStatisticsForAccount(Account $account)
    {
        $noOfWords = Word::where('account_id', '=', $account->id)
            ->count();

        $noOfTranslations = Translation::notDeleted()
            ->where('account_id', $account->id)
            ->count();

        $noOfSentences = Sentence::approved()
            ->where('account_id', $account->id)
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