<?php

namespace App\Http\Discuss\Contexts;

use App\Helpers\LinkHelper;
use App\Http\Discuss\IDiscussContext;
use App\Models\Account;
use Illuminate\Database\Eloquent\Model;

class AccountContext implements IDiscussContext
{
    private LinkHelper $_linkHelper;

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

    public function resolveById(int $entityId)
    {
        return Account::find($entityId);
    }

    public function available($entityOrId, ?Account $account = null)
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
        return 'person';
    }

    public function view(Model $entity)
    {
        return view('discuss.context._account', [
            'account' => $entity,
            'address' => $this->resolve($entity),
        ]);
    }
}
