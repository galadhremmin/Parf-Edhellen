<?php

namespace App\Http\RouteResolving\Resolvers;

use Illuminate\Database\Eloquent\Model;
use App\Http\RouteResolving\IRouteResolver;
use App\Helpers\LinkHelper;

class TranslationRouteResolver implements IRouteResolver
{
    private $_linkHelper;

    public function __construct(LinkHelper $linkHelper)
    {
        $this->_linkHelper = $linkHelper;
    }

    public function resolve(Model $entity)
    {
        return $this->_linkHelper->translationVersions($entity->id);
    }

    public function getRoles()
    {
        return [];
    }

    public function getName(Model $entity)
    {
        if (! $entity) {
            return null;
        }
        
        return 'Gloss “'.$entity->word->word.'” by '.$entity->account->nickname;
    }

    public function getIconPath()
    {
        // Refer to Bootstrap glyphicons.
        return 'book';
    }
}
