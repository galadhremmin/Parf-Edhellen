<?php

namespace App\Http\RouteResolving\Resolvers;

use Illuminate\Database\Eloquent\Model;
use App\Http\RouteResolving\IRouteResolver;
use App\Helpers\LinkHelper;

class AccountRouteResolver implements IRouteResolver
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

        return $this->_linkHelper->author($entity->id, $entity->nickname);
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

        return 'Account “'.$entity->nickname.'”';
    }

    public function getIconPath()
    {
        // Refer to Bootstrap glyphicons.
        return 'user';
    }
}
