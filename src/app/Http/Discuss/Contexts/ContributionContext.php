<?php

namespace App\Http\Discuss\Contexts;

use Illuminate\Database\Eloquent\Model;

use App\Http\Discuss\IDiscussContext;
use App\Models\{
    Account,
    Contribution
};

class ContributionContext implements IDiscussContext
{
    public function resolve(Model $entity)
    {
        if (! $entity) {
            return null;
        }

        return route('contribution.show', ['id' => $entity->id]);
    }

    public function resolveById(int $entityId)
    {
        return Contribution::find($entityId);
    }

    public function available($entityOrId, Account $account = null)
    {
        if ($account === null) {
            return false;
        }

        $accountId = 0;
        if (is_numeric($entityOrId)) {
            // TODO: Optimize. Somehow.
            $accountId = Contribution::where('id', $entityOrId)
                ->pluck('account_id')
                ->first();
        } else {
            $accountId = $entityOrId->account_id;
        }

        return $accountId === $account->id || $account->isAdministrator();
    }

    public function getName(Model $entity)
    {
        if (! $entity) {
            return null;
        }
        
        return 'Contribution “'.$entity->word.'” by '.$entity->account->nickname;
    }

    public function getIconPath()
    {
        // Refer to Bootstrap glyphicons.
        return 'book';
    }

    public function view(Model $entity)
    {
        return view('discuss.context._contribution', [
            'contribution' => $entity,
            'address'      => $this->resolve($entity)
        ]);
    }
}
