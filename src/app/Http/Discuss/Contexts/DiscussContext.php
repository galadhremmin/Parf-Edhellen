<?php

namespace App\Http\Discuss\Contexts;

use App\Http\Discuss\IDiscussContext;
use App\Models\Account;
use App\Models\ForumDiscussion;
use Illuminate\Database\Eloquent\Model;

class DiscussContext implements IDiscussContext
{
    public function resolve(Model $entity)
    {
        return route('discuss.find-thread', ['id' => $entity->id]);
    }

    public function resolveById(int $entityId)
    {
        $thread = ForumDiscussion::find($entityId);
        if ($thread === null) {
            $thread = new ForumDiscussion;
        }

        return $thread;
    }

    public function available($entityOrId, ?Account $account = null)
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
            'post' => $entity,
        ]);
    }
}
