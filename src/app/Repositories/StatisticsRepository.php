<?php

namespace App\Repositories;

use App\Models\{ 
    Account, 
    FlashcardResult,
    ForumPost, 
    ForumPostLike, 
    Gloss, 
    Sentence, 
    Word
};

class StatisticsRepository
{
    public function getStatisticsForAccount(Account $account)
    {
        $noOfWords = Word::forAccount($account->id)
            ->count();

        $noOfGlosses = Gloss::notDeleted()
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
            'noOfWords'      => $noOfWords,
            'noOfGlosses'    => $noOfGlosses,
            'noOfSentences'  => $noOfSentences,
            'noOfThanks'     => $noOfThanks,
            'noOfFlashcards' => $noOfFlashcards,
            'noOfPosts'      => $noOfPosts
        ];
    }
}
