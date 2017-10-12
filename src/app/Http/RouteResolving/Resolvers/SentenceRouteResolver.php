<?php

namespace App\Http\RouteResolving\Resolvers;

use Illuminate\Database\Eloquent\Model;
use App\Http\RouteResolving\IRouteResolver;
use App\Helpers\LinkHelper;

class SentenceRouteResolver implements IRouteResolver
{
    private $_linkHelper;

    public function __construct(LinkHelper $linkHelper)
    {
        $this->_linkHelper = $linkHelper;
    }

    public function resolve(Model $entity)
    {
        if (! $entity) {
            return null;
        }

        return $this->_linkHelper->sentence($entity->language_id, $entity->language->name, 
            $entity->id, $entity->name);
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

        // Some sentences actually do lack an author (as they were imported). SO make sure one exists before adding 'by'.
        return 'Phrase “'.$entity->name.'”' + ($entity->account_id ? ' by '.$entity->account->nickname : '');
    }

    public function getIconPath()
    {
        // Refer to Bootstrap glyphicons.
        return 'align-justify';
    }
}
