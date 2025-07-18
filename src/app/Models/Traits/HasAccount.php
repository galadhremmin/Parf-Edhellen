<?php

namespace App\Models\Traits;

use App\Models\Account;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasAccount
{
    public function scopeForAccount($query, $accountOrId)
    {
        $accountId = $this->getAccountId($accountOrId);
        $query->where('account_id', $accountId);
    }

    public function scopeForAccounts($query, array $accounts)
    {
        $accountIds = [];
        foreach ($accounts as $account) {
            $accountIds[] = $this->getAccountId($account);
        }

        $query->whereIn('account_id', $accountIds);
    }

    /**
     * @return BelongsTo<Account>
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class)
            ->select(['id', 'nickname', 'tengwar', 'has_avatar']);
    }

    private function getAccountId($accountOrId)
    {
        if (is_numeric($accountOrId)) {
            return intval($accountOrId);
        }

        if ($accountOrId instanceof Account) {
            return $accountOrId->id;
        }

        throw new \Exception('Invalid account parameter "'.serialize($accountOrId).'".');
    }
}
