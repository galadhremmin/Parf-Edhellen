<?php

namespace App\Http\Discuss\Contexts;

use Illuminate\Database\Eloquent\Model;

use App\Models\Account;
use App\Http\Discuss\IDiscussContext;

class DiscussContext implements IDiscussContext
{
    public function resolve(Model $entity)
    {
        return route('discuss.find-thread', ['id' => $entity->id]);
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

    public function view(Model $entity)
    {
        return view('discuss.context._discuss', [
            'post' => $entity
        ]);
    }
}
