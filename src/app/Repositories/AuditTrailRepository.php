<?php

namespace App\Repositories;

use App\Helpers\LinkHelper;
use App\Models\AuditTrail;
use App\Models\Initialization\Morphs;
use App\Models\Interfaces\IHasFriendlyName;
use App\Models\ModelBase;
use App\Models\Account;
use Illuminate\Auth\AuthManager;

class AuditTrailRepository implements Interfaces\IAuditTrailRepository
{
    protected LinkHelper $_link;

    protected AuthManager $_authManager;

    public function __construct(LinkHelper $link, AuthManager $authManager)
    {
        $this->_link = $link;
        $this->_authManager = $authManager;
    }

    public function get(int $noOfRows, int $skipNoOfRows = 0, array $action_ids = [])
    {
        $query = AuditTrail::orderBy('id', 'desc')
            ->with([
                'account' => function ($query) {
                    $query->select('id', 'nickname', 'has_avatar');
                },
                'entity' => function () {},
            ]);

        if (! $this->_authManager->check() || ! $this->_authManager->user()->isAdministrator()) {
            // Put audit trail actions here that only administrators should see.
            $query = $query->where('is_admin', 0);
        }

        if (count($action_ids) > 0) {
            $query = $query->whereIn('action_id', $action_ids);
        }

        $actions = $query->skip($skipNoOfRows)->take($noOfRows)->get();

        return $actions;
    }

    public function store(int $action, $entity, int $userId = 0, ?bool $is_elevated = null, ?array $data = null)
    {
        if ($userId === 0) {
            // Is the user authenticated?
            if (! $this->_authManager->check()) {
                if (($entity instanceof ModelBase && $entity->hasAttribute('account_id')) ||
                     property_exists($entity, 'account_id')) {
                    $userId = $entity->account_id;
                }

                if (! $userId) {
                    return;
                }
            } else {
                $userId = $this->_authManager->user()->id;
            }
        }

        // Retrieve the associated morph map key based on the specified entity.
        $typeName = Morphs::getAlias($entity);
        if ($typeName === null) {
            throw new \Exception(get_class($entity).' is not supported.');
        }

        if ($is_elevated === null) {
            // check whether the specified user is an administrator
            $request = request();
            $is_elevated = false;
            if ($request !== null) {
                $user = $request->user();
                $is_elevated = $user !== null && $user->isIncognito();
            }
        }

        if ($is_elevated === null) {
            $is_elevated = false;
        }

        $entityName = null;
        if ($entity instanceof IHasFriendlyName) {
            $entityName = $entity->getFriendlyName();
        }

        AuditTrail::create([
            'account_id' => $userId,
            'entity_id' => $entity->id,
            'entity_type' => $typeName,
            'entity_name' => $entityName,
            'action_id' => $action,
            'is_admin' => $is_elevated,
            'data' => is_array($data) ? json_encode($data) : null,
        ]);
    }

    public function hideForAccount(Account $account): void
    {
        AuditTrail::where('account_id', $account->id)
            ->update(['is_admin' => true]);
    }
}
