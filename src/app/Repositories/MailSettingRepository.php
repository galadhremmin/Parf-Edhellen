<?php

namespace App\Repositories;

use App\Models\Account;
use App\Models\Initialization\Morphs;
use App\Models\MailSetting;
use App\Models\MailSettingOverride;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class MailSettingRepository
{
    /**
     * Returns the e-mail addresses for the accounts which qualify for the specified event and entity.
     *
     * @param  App\Models\ModelBase  $entity
     */
    public function qualify(array $accountIds, string $event, $entity): Collection
    {
        $alias = $this->getMorph($entity);
        $settings = MailSetting::forAccounts($accountIds)
            ->select('account_id', $event)->get();
        $overrides = MailSettingOverride::forAccounts($accountIds)
            ->where([
                ['entity_type', $alias],
                ['entity_id', $entity->id],
            ])->get();

        $currentUser = Auth::user();
        $currentUserId = 0;
        if ($currentUser !== null) {
            $currentUserId = $currentUser->id;
        }

        $ids = [];
        foreach ($accountIds as $id) {
            $setting = $settings->firstWhere('account_id', $id);
            $override = $overrides->firstWhere('account_id', $id);

            if (($override && $override->disabled === 1) ||
                (! $override && $setting && $setting->getAttributeValue($event) === 0)) {
                continue;
            }

            // don't have the current user being e-mailed by their own
            // notifications.
            if ($id !== $currentUserId) {
                $ids[] = $id;
            }
        }

        if (empty($ids)) {
            return new Collection;
        }

        return Account::whereIn('id', $ids)
            ->select('id', 'email')
            ->distinct()
            ->get()
            ->filter(function ($v) {
                return filter_var($v->email, FILTER_VALIDATE_EMAIL) !== false;
            });
    }

    /**
     * Checks whether the specified account has a configuration override associated with the specified entity.
     *
     * @param  App\Models\ModelBase  $entity
     * @return bool
     */
    public function getOverride(int $accountId, $entity)
    {
        $morph = $this->getMorph($entity);

        return MailSettingOverride::forAccount($accountId)
            ->where([
                'entity_type' => $morph,
                'entity_id' => $entity->id,
            ])
            ->first();
    }

    /**
     * Checks whether the account can be notified about the change to the specified entity.
     *
     * @param  App\Models\ModelBase  $entity
     */
    public function canNotify(int $accountId, $entity): bool
    {
        $morph = $this->getMorph($entity);

        $override = MailSettingOverride::forAccount($accountId)
            ->where([
                ['entity_type', $morph],
                ['entity_id', $entity->id],
            ])
            ->first();

        return ! $override || $override->disabled === 0;
    }

    /**
     * Enables or disables notification for the specified entity, for the specified account.
     *
     * @param  mixed  $entity
     */
    public function setNotifications(int $accountId, $entity, ?bool $notificationEnabled): bool
    {
        $morph = $this->getMorph($entity);

        if ($notificationEnabled === null) {
            MailSettingOverride::where([
                'account_id' => $accountId,
                'entity_type' => $morph,
                'entity_id' => $entity->id,
            ])->delete();

            return false;
        }

        $override = MailSettingOverride::updateOrCreate([
            'account_id' => $accountId,
            'entity_type' => $morph,
            'entity_id' => $entity->id,
        ]);
        $override->disabled = ! $notificationEnabled;
        $override->save();

        return ! $override->disabled;
    }

    /**
     * Generates a cancellation token for the specified entity and user account.
     *
     * @param  mixed  $entity
     */
    public function generateCancellationToken(int $accountId, $entity): string
    {
        $morph = $this->getMorph($entity);
        $token = [
            'id' => $accountId,
            'ea' => $morph,
            'eid' => $entity->id,
        ];
        $token['x'] = Hash::make(serialize($token));

        return encrypt($token);
    }

    /**
     * Processes the specified token and creates a setting override for the entity
     * it refers to.
     */
    public function handleCancellationToken(string $token): bool
    {
        try {
            $token = decrypt($token);
        } catch (DecryptException $ex) {
            return false;
        }

        if (! is_array($token)) {
            return false;
        }

        $validator = Validator::make($token, [
            'x' => 'required',
            'id' => 'required|numeric|exists:accounts,id',
            'ea' => 'required|string',
            'eid' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return false;
        }

        $hash = $token['x'];
        unset($token['x']);
        if (! Hash::check(serialize($token), $hash)) {
            return false;
        }

        $modelName = Morphs::getMorphedModel($token['ea']);
        $model = resolve($modelName)->find($token['eid']);

        if (! $model) {
            return false;
        }

        $override = MailSettingOverride::updateOrCreate([
            'account_id' => $token['id'],
            'entity_type' => $token['ea'],
            'entity_id' => $token['eid'],
        ]);
        $override->disabled = 1;
        $override->save();

        return true;
    }

    /**
     * Gets the morph alias for the specified entity. Throws an exception if the entity
     * is not supported.
     *
     * @param  mixed  $entity
     */
    protected function getMorph($entity): string
    {
        $morph = Morphs::getAlias($entity);
        if (! $morph || ! $entity->id) {
            throw new \Exception('Unsupported entity.');
        }

        return $morph;
    }
}
