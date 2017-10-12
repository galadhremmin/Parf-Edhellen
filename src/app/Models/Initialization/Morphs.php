<?php

namespace App\Models\Initialization;

use Illuminate\Database\Eloquent\Relations\Relation;
use App\Models\{ 
    Account, 
    AuditTrail, 
    Contribution,
    Favourite, 
    FlashcardResult, 
    ForumPost, 
    Sentence, 
    Translation 
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
            'translation'  => Translation::class
        ]);
    }

    /**
     * Retrieves an alias for the specified entity. Returns null if no alias was found.
     *
     * @param Model $entity
     * @return string|null
     */
    public static function getAlias($entity)
    {
        $map = Relation::morphMap();
        
        foreach ($map as $name => $className) {
            if (is_a($entity, $className)) {
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
