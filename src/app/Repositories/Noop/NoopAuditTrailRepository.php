<?php

namespace App\Repositories\Noop;

use App\Models\{ Account, AuditTrail, Favourite, FlashcardResult, ForumContext, ForumPost, Sentence, Translation };
use App\Repositories\Interfaces\IAuditTrailRepository;
use Illuminate\Database\Eloquent\Relations\Relation;

class NoopAuditTrailRepository implements IAuditTrailRepository
{
    public function mapMorphs() 
    {
        Relation::morphMap([
            'account'     => Account::class,
            'favourite'   => Favourite::class,
            'forum'       => ForumPost::class,
            'sentence'    => Sentence::class,
            'translation' => Translation::class,
            'flashcard'   => FlashcardResult::class
        ]);
    }

    public function get(int $noOfRows, int $skipNoOfRows = 0, $previousItem = null)
    {
        // Noop
    }

    public function store(int $action, $entity, int $userId = 0, bool $is_elevated = null)
    {
        // Noop
    }
}
