<?php

namespace App\Http\RouteResolving;

use App\Models\Initialization\Morphs;

class RouteResolverFactory
{
    private static $_cache = [];

    /**
     * Creates an instance of IRouteResolver for the specified morph alias.
     *
     * @param string $morph
     * @return \App\Http\RouteResolvers\IRouteResolver
     */
    public function create(string $morph)
    {
        if (isset(self::$_cache[$morph])) {
            $routeResolver = self::$_cache[$morph];

        } else {
            $className = Morphs::getMorphedModel($morph);
            if (! $className) {
                return null;
            }

            $entities = config('ed.forum_entities');
            if (! is_array($entities) || ! isset($entities[$className])) {
                return null;
            }

            $routeResolver = self::$_cache[$morph] = $entities[$className];
        }

        return resolve($routeResolver);
    }
}
