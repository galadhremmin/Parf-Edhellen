<?php

namespace App\Models\Initialization;

use Illuminate\Database\Eloquent\Relations\Relation;
use App\Models\{ 
    Account, 
    AuditTrail, 
    Contribution,
    FlashcardResult,
    Gloss, 
    ForumDiscussion,
    ForumGroup,
    ForumThread,
    ForumPost,
    GlossInflection,
    Sentence,
    SentenceFragment,
    Sense,
    Word
};
use App\Models\Versioning\{
    GlossVersion,
    GlossDetailVersion,
    TranslationVersion
};
use Illuminate\Support\Enumerable;

class Morphs 
{
    public static function map() 
    {
        Relation::morphMap([
            'account'      => Account::class,
            'contribution' => Contribution::class,
            'flashcard'    => FlashcardResult::class,
            'sentence'     => Sentence::class,
            'fragment'     => SentenceFragment::class,
            'gloss'        => Gloss::class,
            'gloss_infl'   => GlossInflection::class,
            'discussion'   => ForumDiscussion::class,
            'forum_group'  => ForumGroup::class,
            'forum_thread' => ForumThread::class,
            'forum'        => ForumPost::class,
            'sense'        => Sense::class,
            'word'         => Word::class,

            'glossv'       => GlossVersion::class,
            'glossdetailv' => GlossDetailVersion::class,
            'translationv' => TranslationVersion::class
        ]);
    }

    /**
     * Retrieves an alias for the specified entity. Returns null if no alias was found.
     *
     * @param Model|string $entity
     * @return string|null
     */
    public static function getAlias($entityOrClassName, $inferArrays = true)
    {
        if ($inferArrays) {
            if (is_array($entityOrClassName)) {
                if (count($entityOrClassName) < 1) {
                    return null;
                }

                return self::getAlias($entityOrClassName[array_key_first($entityOrClassName)]);
            } else if ($entityOrClassName instanceof Enumerable) {
                return self::getAlias($entityOrClassName->first());
            }
        } else if (is_array($entityOrClassName) || $entityOrClassName instanceof Enumerable) {
            return null;
        }

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
