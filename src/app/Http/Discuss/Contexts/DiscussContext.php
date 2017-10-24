<?php

namespace App\Http\Discuss\Contexts;

use Illuminate\Database\Eloquent\Model;

use App\Http\Discuss\IDiscussContext;

class DiscussContext implements IDiscussContext
{
    public function resolve(Model $entity)
    {
        
    }

    function available($entityOrId, Account $account = null)
    {
        return true;
    }

    public function getName(Model $entity)
    {
        return null;
    }

    public function getIconPath()
    {
        // Refer to Bootstrap glyphicons.
        return 'comment';
    }
}
