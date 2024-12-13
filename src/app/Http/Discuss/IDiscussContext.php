<?php

namespace App\Http\Discuss;

use View;

use Illuminate\Database\Eloquent\Model;
use App\Models\{
    Account,
    ModelBase
};

interface IDiscussContext
{
    /**
     * Gets the path URI component for the specified entity associated with the context. 
     *
     * @param Model $entity
     * @return string
     */
    function resolve(Model $entity);

    /**
     * Gets the ModelBase for the specified entity associated with the context. 
     *
     * @param int $entityId
     * @return ModelBase
     */
    function resolveById(int $entityId);

    /**
     * Gets a list of roles that the user must have in order to successfully access the content.
     * Note: the account would qualify if it is a member in _at least_ one of the specified roles.
     *
     * @param Model|int $entity
     * @param Account $account
     * @return bool 
     */
    function available($entityOrId, ?Account $account = null);

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

    /**
     * View for representing the specified entitity.
     *
     * @param Model $entity
     * @return View
     */
    function view(Model $entity);
}
