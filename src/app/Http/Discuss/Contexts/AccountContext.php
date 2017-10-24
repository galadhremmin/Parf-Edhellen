<?php

namespace App\Http\Discuss\Contexts;

use Illuminate\Database\Eloquent\Model;

use App\Models\Account;
use App\Http\Discuss\IDiscussContext;
use App\Helpers\LinkHelper;

class AccountContext implements IDiscussContext
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

    function available($entityOrId, Account $account = null)
    {
        return true;
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
