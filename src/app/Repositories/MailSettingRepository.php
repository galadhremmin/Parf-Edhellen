<?php

namespace App\Repositories;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

use App\Helpers\StringHelper;
use App\Models\{ 
    Account,
    MailSetting,
    MailSettingOverride 
};
use App\Models\Initialization\Morphs;

class MailSettingRepository
{
    /**
     * Returns the e-mail addresses for the accounts which qualify for the specified event and entity.
     *
     * @param array $accountIds
     * @param string $event
     * @param App\Models\ModelBase $entity
     * @return array
     */
    public function qualify(array $accountIds, string $event, $entity)
    {
        $alias = $this->getMorph($entity);
        $settings = MailSetting::forAccounts($accountIds)
            ->select('account_id', $event)->get();
        $overrides = MailSettingOverride::forAccounts($accountIds)
            ->where([
                ['entity_type', $alias],
                ['entity_id', $entity->id]
            ])->get();

        $ids = [];
        foreach ($accountIds as $id) {
            $setting = $settings->firstWhere('account_id', $id);
            $override = $overrides->firstWhere('account_id', $id);

            if (($override && $override->disabled === 1) ||
                (! $override && $setting && $setting->getAttributeValue($event) === 0)) {
                continue;
            }

            $ids[] = $id;
        }

        if (empty($ids)) {
            return [];
        }

        return Account::whereIn('id', $ids)
            ->select('email')
            ->distinct()
            ->pluck('email')
            ->toArray();
    }

    /**
     * Checks whether the account can be notified about the change to the specified entity.
     *
     * @param integer $accountId
     * @param App\Models\ModelBase $entity
     * @return boolean
     */
    public function canNotify(int $accountId, $entity)
    {
        $morph = $this->getMorph($entity);
        
        $override = MailSettingOverride::forAccount($accountId)
            ->where([
                ['entity_type', $morph],
                ['entity_id', $entity->id]
            ])
            ->first();
        return ! $override || $override->disabled === 0;
    }

    /**
     * Enables or disables notification for the specified entity, for the specified account.
     *
     * @param integer $accountId
     * @param mixed $entity
     * @param boolean $notificationEnabled
     * @return boolean
     */
    public function setNotifications(int $accountId, $entity, bool $notificationEnabled)
    {
        $morph = $this->getMorph($entity);

        $override = MailSettingOverride::updateOrCreate([
            'account_id'  => $accountId,
            'entity_type' => $morph,
            'entity_id'   => $entity->id,
            'disabled'    => ! $notificationEnabled
        ]);

        return ! $override->disabled;
    }

    /**
     * Gets the morph alias for the specified entity. Throws an exception if the entity
     * is not supported.
     *
     * @param mixed $entity
     * @return string
     */
    protected function getMorph($entity)
    {
        $morph = Morphs::getAlias($entity);
        if (! $morph || ! $entity->hasAttribute('id')) {
            throw new \Exception('Unsupported entity.');
        }

        return $morph;
    }
}