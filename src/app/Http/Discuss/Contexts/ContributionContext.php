<?php

namespace App\Http\Discuss\Contexts;

use App\Http\Controllers\Contributions\ContributionControllerFactory;
use App\Http\Controllers\Resources\ContributionController;
use Illuminate\Database\Eloquent\Model;

use App\Http\Discuss\IDiscussContext;
use App\Models\{
    Account,
    Contribution
};

class ContributionContext implements IDiscussContext
{
    private $_contributionController;
    public function __construct(ContributionController $contributionController) {
        $this->_contributionController = $contributionController;
    }

    public function resolve(Model $entity)
    {
        if (! $entity) {
            return null;
        }

        return route('contribution.show', ['contribution' => $entity->id]);
    }

    public function resolveById(int $entityId)
    {
        return Contribution::find($entityId);
    }

    public function available($entityOrId, Account $account = null)
    {
        /*
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
        */
        // Commenting on contributions are now possible for everyone
        return true;
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
        $model = ContributionControllerFactory::createController($entity->type)->getViewModel($entity);
        return view('discuss.context._contribution', $model->toModelArray() + [
            'address' => $this->resolve($entity)
        ]);
    }
}
