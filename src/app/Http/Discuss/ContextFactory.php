<?php

namespace App\Http\Discuss;

use App\Models\Initialization\Morphs;

class ContextFactory
{
    private static $_cache = [];

    /**
     * Creates an instance of IDiscussContext for the specified morph alias.
     *
     * @param string $morph
     * @return \App\Http\Discuss\IDiscussContext
     */
    public function create(string $morph)
    {
        if (isset(self::$_cache[$morph])) {
            $contextName = self::$_cache[$morph];

        } else {
            $className = Morphs::getMorphedModel($morph);
            if (! $className) {
                return null;
            }

            $entities = config('ed.forum_entities');

            if (! is_array($entities) || ! isset($entities[$className])) {
                return null;
            }

            $contextName = self::$_cache[$morph] = $entities[$className];
        }

        return resolve($contextName);
    }
}
