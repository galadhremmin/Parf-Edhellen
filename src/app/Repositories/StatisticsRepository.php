<?php

namespace App\Repositories;

use App\Models\{ Account, ForumPost, ForumPostLike, Sentence, Translation, Word, FlashcardResult };

class StatisticsRepository
{
    public function getStatisticsForAccount(Account $account)
    {
        $noOfWords = Word::forAccount($account->id)
            ->count();

        $noOfTranslations = Translation::notDeleted()
            ->forAccount($account->id)
            ->count();

        $noOfSentences = Sentence::approved()
            ->forAccount($account->id)
            ->count();

        $noOfThanks = ForumPostLike::forAccount($account->id)
            ->count();

        $noOfFlashcards = FlashcardResult::forAccount($account->id)
            ->count();

        $noOfPosts = ForumPost::forAccount($account->id)
            ->count();

        return [
            'noOfWords'        => $noOfWords,
            'noOfTranslations' => $noOfTranslations,
            'noOfSentences'    => $noOfSentences,
            'noOfThanks'       => $noOfThanks,
            'noOfFlashcards'   => $noOfFlashcards,
            'noOfPosts'        => $noOfPosts
        ];
    }
}
