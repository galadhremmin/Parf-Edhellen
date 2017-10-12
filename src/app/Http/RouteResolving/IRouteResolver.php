<?php

namespace App\Http\RouteResolving;
use Illuminate\Database\Eloquent\Model;

interface IRouteResolver
{
    /**
     * Gets the path URI component for the specified entity. 
     *
     * @param Model $entity
     * @return string
     */
    function resolve(Model $entity);

    /**
     * Gets a list of roles that the user must have in order to successfully access the content.
     * Note: the account would qualify if it is a member in _at least_ one of the specified roles.
     *
     * @return array
     */
    function getRoles();

    /**
     * Gets a friendly name for the route.
     *
     * @param Model $entity
     * @return string
     */
    function getName(Model $entity);

    /**
     * Gets the path to an icon representing the entity.
     *
     * @return string|null
     */
    function getIconPath();
}
