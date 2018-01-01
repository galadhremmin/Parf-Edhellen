<?php

namespace App\Models\Initialization;

use Illuminate\Database\Eloquent\Relations\Relation;
use App\Models\{ 
    Account, 
    AuditTrail, 
    Contribution,
    Favourite, 
    FlashcardResult,
    Gloss, 
    ForumDiscussion,
    ForumThread,
    ForumPost, 
    Sentence 
};

class Morphs 
{
    public static function map() 
    {
        Relation::morphMap([
            'account'      => Account::class,
            'contribution' => Contribution::class,
            'favourite'    => Favourite::class,
            'flashcard'    => FlashcardResult::class,
            'forum'        => ForumPost::class,
            'sentence'     => Sentence::class,
            'gloss'        => Gloss::class,
            'discussion'   => ForumDiscussion::class,
            'forum_thread' => ForumThread::class
        ]);
    }

    /**
     * Retrieves an alias for the specified entity. Returns null if no alias was found.
     *
     * @param Model|string $entity
     * @return string|null
     */
    public static function getAlias($entityOrClassName)
    {
        $map = Relation::morphMap();
        $entityClassName = is_string($entityOrClassName)
            ? $entityOrClassName : get_class($entityOrClassName);

        foreach ($map as $name => $className) {
            if ($className === $entityClassName) {
                return $name;
            }
        }

        return null;
    }

    /**
     * Attempts to find the model name associated with the specified alias.
     *
     * @param string $alias
     * @return string
     */
    public static function getMorphedModel(string $alias)
    {
        return Relation::getMorphedModel($alias);
    }
}
