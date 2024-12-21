<?php

namespace App\Models\Initialization;

use App\Models\Account;
use App\Models\Contribution;
use App\Models\FlashcardResult;
use App\Models\ForumDiscussion;
use App\Models\ForumGroup;
use App\Models\ForumPost;
use App\Models\ForumThread;
use App\Models\Gloss;
use App\Models\GlossInflection;
use App\Models\Sense;
use App\Models\Sentence;
use App\Models\SentenceFragment;
use App\Models\Versioning\GlossDetailVersion;
use App\Models\Versioning\GlossVersion;
use App\Models\Versioning\TranslationVersion;
use App\Models\Word;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Enumerable;

class Morphs
{
    public static function map()
    {
        Relation::morphMap([
            'account' => Account::class,
            'contribution' => Contribution::class,
            'flashcard' => FlashcardResult::class,
            'sentence' => Sentence::class,
            'fragment' => SentenceFragment::class,
            'gloss' => Gloss::class,
            'gloss_infl' => GlossInflection::class,
            'discussion' => ForumDiscussion::class,
            'forum_group' => ForumGroup::class,
            'forum_thread' => ForumThread::class,
            'forum' => ForumPost::class,
            'sense' => Sense::class,
            'word' => Word::class,

            'glossv' => GlossVersion::class,
            'glossdetailv' => GlossDetailVersion::class,
            'translationv' => TranslationVersion::class,
        ]);
    }

    /**
     * Retrieves an alias for the specified entity. Returns null if no alias was found.
     *
     * @param  Model|string  $entity
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
            } elseif ($entityOrClassName instanceof Enumerable) {
                return self::getAlias($entityOrClassName->first());
            }
        } elseif (is_array($entityOrClassName) || $entityOrClassName instanceof Enumerable) {
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
     */
    public static function getMorphedModel(string $alias): string
    {
        return Relation::getMorphedModel($alias);
    }

    public static function getMorphToMap()
    {
        $map = Relation::morphMap();
        $morphs = array_keys($map);

        return array_reduce($morphs, function ($carry, $morph) use ($map) {
            if (array_key_exists($map[$morph], $carry)) {
                $carry[$map[$morph]][] = $morph;
                dd($morph);
            } else {
                $carry[$map[$morph]] = [$morph];
            }

            return $carry;
        }, []);
    }
}
