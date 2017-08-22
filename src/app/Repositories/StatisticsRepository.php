<?php

namespace App\Repositories;

use App\Models\{ Account, ForumPostLike, Sentence, Translation, Word, FlashcardResult };

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

        $noOfThanks = ForumPostLike::where('account_id', $account->id)
            ->count();

        $noOfFlashcards = FlashcardResult::where('account_id', $account->id)
            ->count();

        return [
            'noOfWords'        => $noOfWords,
            'noOfTranslations' => $noOfTranslations,
            'noOfSentences'    => $noOfSentences,
            'noOfThanks'       => $noOfThanks,
            'noOfFlashcards'   => $noOfFlashcards
        ];
    }
}