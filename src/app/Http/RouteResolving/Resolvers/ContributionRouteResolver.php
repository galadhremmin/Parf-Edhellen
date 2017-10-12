<?php

namespace App\Http\RouteResolving\Resolvers;

use App\Http\RouteResolving\IRouteResolver;
use Illuminate\Database\Eloquent\Model;

class ContributionRouteResolver implements IRouteResolver
{
    public function resolve(Model $entity)
    {
        if (! $entity) {
            return null;
        }

        return route('contribution.show', ['id' => $entity->id]);
    }

    public function getRoles()
    {
        return ['Administrators'];
    }

    public function getName(Model $entity)
    {
        if (! $entity) {
            return null;
        }
        
        return 'Contribution “'.$entity->word.'” by '.$entity->account->nickname;
    }

    public function getIconPath()
    {
        // Refer to Bootstrap glyphicons.
        return 'plus';
    }
}
