<?php

namespace App\Http\Discuss;

use App\Models\Account;
use App\Models\ModelBase;
use Illuminate\Database\Eloquent\Model;
use View;

interface IDiscussContext
{
    /**
     * Gets the path URI component for the specified entity associated with the context.
     *
     * @return string
     */
    public function resolve(Model $entity);

    /**
     * Gets the ModelBase for the specified entity associated with the context.
     *
     * @return ModelBase
     */
    public function resolveById(int $entityId);

    /**
     * Gets a list of roles that the user must have in order to successfully access the content.
     * Note: the account would qualify if it is a member in _at least_ one of the specified roles.
     *
     * @param  Model|int  $entity
     * @return bool
     */
    public function available($entityOrId, ?Account $account = null);

    /**
     * Gets a friendly name for the route.
     *
     * @return string
     */
    public function getName(Model $entity);

    /**
     * Gets the path to an icon representing the entity.
     *
     * @return string|null
     */
    public function getIconPath();

    /**
     * View for representing the specified entitity.
     *
     * @return View
     */
    public function view(Model $entity);
}
