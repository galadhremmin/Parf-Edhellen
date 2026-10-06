<?php

namespace App\Security;

use App\Events\AccountAvatarChanged;
use App\Events\AccountDestroyed;
use App\Events\AccountMarkedAsSpammer;
use App\Events\AccountPasswordChanged;
use App\Events\AccountRoleRemove;
use App\Events\AccountsMerged;
use App\Helpers\StorageHelper;
use App\Models\Account;
use App\Models\AuthorizationProvider;
use App\Models\Role;
use App\Repositories\DiscussRepository;
use App\Repositories\Interfaces\IAuditTrailRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Auth\AuthManager;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use InvalidArgumentException;

class AccountManager
{
    private StorageHelper $_storageHelper;

    private AuthManager $_authManager;

    private IAuditTrailRepository $_auditTrailRepository;

    private DiscussRepository $_discussRepository;

    public function __construct(StorageHelper $storageHelper, AuthManager $authManager,
        IAuditTrailRepository $auditTrailRepository, DiscussRepository $discussRepository)
    {
        $this->_storageHelper = $storageHelper;
        $this->_authManager = $authManager;
        $this->_auditTrailRepository = $auditTrailRepository;
        $this->_discussRepository = $discussRepository;
    }

    public function getRootAccount(): ?Account
    {
        $role = Role::where('name', RoleConstants::Root)
            ->first();

        if ($role === null) {
            return null;
        }

        return $role->accounts()->first();
    }

    public function createAccount(string $username, ?string $identity = null, ?int $providerId = null, ?string $password = null, ?string $name = null): Account
    {
        $nickname = $this->getNextAvailableNickname($name);

        if ($providerId !== null) {
            AuthorizationProvider::findOrFail($providerId);
        }

        $identity = ! empty($identity) ? $identity : 'MASTER|'.$username;
        $user = Account::where('identity', $identity)->first();
        if ($user !== null) {
            // this master account already exists so it's probably in the process of being created.
            // We need to perform this check to ensure that this isn't happening twice.
            return $user;
        }

        // Security-sensitive attributes are assigned explicitly rather than through mass
        // assignment (Account::$fillable is intentionally minimal).
        $user = new Account;
        $user->nickname = $nickname;
        $user->email = $username;
        $user->identity = $identity;
        $user->authorization_provider_id = $providerId;
        $user->is_passworded = ! empty($password);
        $user->is_master_account = ! empty($password);
        $user->password = ! empty($password) ? Hash::make($password) : null;
        $user->shows_welcome = true; // a new member gets the welcome checklist on their profile
        $user->save();

        $user->addMembershipTo(RoleConstants::Users);

        // Notify Laravel that this user has been created. This will trigger downstream flows like
        // e-mail verification.
        event(new Registered($user));

        return $user;
    }

    public function createMasterAccount(Account $account): Account
    {
        if ($account->is_master_account) {
            throw new Exception('Attempting to create a master account for a master account. There can only be one master account per account.');
        }

        if ($this->getMasterAccountByEmail($account->email) !== null) {
            throw new Exception(sprintf('A master account already exists for account %d.', $account->id));
        }

        // Security-sensitive attributes are assigned explicitly rather than through mass
        // assignment (Account::$fillable is intentionally minimal).
        $masterAccount = new Account;
        $masterAccount->email = $account->email;
        $masterAccount->nickname = $account->nickname;
        $masterAccount->tengwar = $account->tengwar;
        $masterAccount->profile = $account->profile;
        $masterAccount->has_avatar = (bool) $account->has_avatar;
        $masterAccount->feature_background_url = $account->feature_background_url;
        $masterAccount->email_verified_at = $account->email_verified_at;
        $masterAccount->authorization_provider_id = null;
        $masterAccount->master_account_id = null;
        $masterAccount->identity = 'MASTER|'.$account->email;
        $masterAccount->is_master_account = true;
        $masterAccount->is_passworded = false;
        $masterAccount->save();

        foreach ($account->roles as $role) {
            $masterAccount->addMembershipTo($role->name);
        }

        if ($account->has_avatar) {
            $avatarPath = $this->_storageHelper->getAvatarPath($account->id);
            $newAvatarPath = $this->_storageHelper->getAvatarPath($masterAccount->id);
            if ($avatarPath !== null) {
                copy($avatarPath, $newAvatarPath);
                event(new AccountAvatarChanged($masterAccount));
            }
        }

        event(new Registered($masterAccount));

        return $masterAccount;
    }

    /**
     * Links the accounts to the master account that owns their e-mail address, creating one from
     * `$initiator` when there is none. `$initiator` must have verified the address.
     */
    public function mergeAccounts(Account $initiator, Collection $accounts): ?Account
    {
        if ($accounts->count() < 2) {
            return null;
        }

        if ($initiator->email_verified_at === null) {
            throw new InvalidArgumentException('Only an account with a verified e-mail address can link accounts.');
        }

        if (! $accounts->contains('id', $initiator->id)) {
            throw new InvalidArgumentException('The initiator must be one of the accounts being linked.');
        }

        if ($this->getUnverifiedMasterAccountFor($initiator) !== null) {
            throw new InvalidArgumentException(sprintf('An unverified principal account holds %s.', $initiator->email));
        }

        foreach ($accounts as $account) {
            if ($account->is_deleted || $account->is_spammer) {
                throw new InvalidArgumentException('Accounts flagged as deleted or spammers cannot be merged.');
            }

            if ($account->email !== $initiator->email) {
                throw new InvalidArgumentException('Only accounts that share an e-mail address can be merged.');
            }
        }

        $masterAccount = $this->getVerifiedMasterAccountByEmail($initiator->email)
            ?? $this->createMasterAccount($initiator);

        foreach ($accounts as $account) {
            if ($account->id !== $masterAccount->id) {
                $this->linkAccountToMasterAccount($account, $masterAccount);
            }
        }

        return $masterAccount;
    }

    public function linkAccountToMasterAccount(Account $account, Account $masterAccount)
    {
        if ($account->id === $masterAccount->id) {
            throw new InvalidArgumentException('User is attempting to link a master account to itself.');
        }

        if ($account->is_spammer || $masterAccount->is_spammer) {
            throw new InvalidArgumentException('Accounts flagged as spammers cannot be linked.');
        }

        if ($account->is_deleted || $masterAccount->is_deleted) {
            throw new InvalidArgumentException('Accounts flagged as deleted cannot be linked.');
        }

        $account->nickname = str_replace('(linked)', '', trim($account->nickname)).' (linked)';
        $account->master_account_id = $masterAccount->id;
        $account->save();

        event(new AccountsMerged($masterAccount, collect([$account])));
    }

    /**
     * Flags the specified account as a spammer. This revokes every role it holds (preventing it from
     * logging in or posting), hides its audit trail from public surfaces such as the front page,
     * hides its forum posts, and records the account as a spammer for future reference. The Root role
     * is never revoked.
     */
    public function markAsSpammer(Account $account, int $actingAccountId): void
    {
        // Revoke every role the account holds so it can no longer log in or post.
        foreach ($account->roles()->get() as $role) {
            if ($role->name === RoleConstants::Root) {
                // Root permissions cannot be revoked.
                continue;
            }

            $account->removeMembership($role->name);
            event(new AccountRoleRemove($account, $role->name, $actingAccountId));
        }

        $account->is_spammer = true;
        $account->save();

        // Hide the account's existing activity from public surfaces.
        $this->_auditTrailRepository->hideForAccount($account);
        $this->_discussRepository->hidePostsForAccount($account);

        event(new AccountMarkedAsSpammer($account, $actingAccountId));
    }

    public function updatePassword(Account $account, string $password): Account
    {
        if (! $account->is_master_account) {
            $masterAccount = $this->createMasterAccount($account);
            $this->linkAccountToMasterAccount($account, $masterAccount);
            $account = $masterAccount;
        }

        $account->is_passworded = true;
        $account->password = Hash::make($password);
        $account->save();

        event(new AccountPasswordChanged($account));

        return $account;
    }

    /**
     * Accounts from before 2017 hold `password_hash('<provider id>_<subject>')` instead of the subject.
     * Finds the one the subject proves, by provider and e-mail address as the old code did, and swaps
     * the hash for the subject.
     */
    public function claimLegacyIdentity(int $providerId, string $subject, ?string $email): ?Account
    {
        if (empty($email)) {
            return null;
        }

        $account = Account::where('authorization_provider_id', $providerId)
            ->where('email', $email)
            ->where('identity', 'like', '$2y$%')
            ->get()
            ->first(fn (Account $candidate) => password_verify($providerId.'_'.$subject, $candidate->identity));

        if ($account !== null) {
            $account->identity = $subject;
            $account->save();
        }

        return $account;
    }

    public function getMasterAccountByEmail(?string $username): ?Account
    {
        if (empty($username)) {
            return null;
        }

        return Account::where('email', $username)
            ->where('is_master_account', true)
            ->first();
    }

    /**
     * Gets the master account that owns the e-mail address. Only a verified master owns its address.
     */
    public function getVerifiedMasterAccountByEmail(?string $email): ?Account
    {
        if (empty($email)) {
            return null;
        }

        return Account::where('email', $email)
            ->where('is_master_account', true)
            ->whereNotNull('email_verified_at')
            ->first();
    }

    /**
     * Gets an unverified master account, other than `$account`, that holds `$account`'s e-mail address.
     */
    public function getUnverifiedMasterAccountFor(Account $account): ?Account
    {
        if (empty($account->email)) {
            return null;
        }

        return Account::where('email', $account->email)
            ->where('is_master_account', true)
            ->whereNull('email_verified_at')
            ->where('id', '<>', $account->id)
            ->first();
    }

    /**
     * Strips an unverified master account of the e-mail address that `$claimant` has verified. The
     * account and its content remain, but it can no longer sign in with a password or a passkey,
     * since both look the account up by its address.
     */
    public function releaseEmailAddress(Account $holder, Account $claimant): void
    {
        if ($claimant->email_verified_at === null || $claimant->email !== $holder->email) {
            throw new InvalidArgumentException('Only an account that has verified the address can release it.');
        }

        if (! $holder->is_master_account || $holder->email_verified_at !== null || $holder->id === $claimant->id) {
            throw new InvalidArgumentException(sprintf('Account %d does not hold an unverified claim to the address.', $holder->id));
        }

        // Accounts linked to the holder may belong to the claimant; that needs a person to untangle.
        if ($holder->linked_accounts()->exists()) {
            throw new InvalidArgumentException(sprintf('Account %d has linked accounts and cannot be released automatically.', $holder->id));
        }

        $holder->email = null;
        $holder->identity = 'RELEASED|'.$holder->id; // frees `MASTER|<e-mail>` for the owner's master account
        $holder->is_master_account = false;
        $holder->setRememberToken(Str::random(60));
        $holder->save();

        if (config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))->where('user_id', $holder->id)->delete();
        }
    }

    public function checkPasswordWithUsername(string $username, string $password): bool
    {
        $account = self::getMasterAccountByEmail($username);
        if ($account === null) {
            return false;
        }

        return self::checkPasswordWithAccount($account, $password);
    }

    public function checkPasswordWithAccount(Account $account, string $password): bool
    {
        return Hash::check($password, $account->password);
    }

    public function delete(Account $account)
    {
        $accountName = $account->getFriendlyName();

        try {
            DB::beginTransaction();

            $uuid = 'DELETED|'.Str::uuid();
            $date = Carbon::now()->toDateTimeString();
            $accountId = $account->id;

            $account->is_deleted = true;
            $account->nickname = sprintf('(Deleted %s)', $date);
            $account->email = 'deleted@'.$uuid;
            $account->authorization_provider_id = null;
            $account->identity = $uuid;
            $account->profile = 'The user deleted their account on '.$date;
            $account->tengwar = null;
            $account->has_avatar = 0;
            $account->is_master_account = false;
            $account->is_passworded = false;
            $account->master_account_id = null;
            $account->password = null;
            $account->save();

            $linkedAccounts = Account::where('master_account_id', $accountId)
                ->get();

            foreach ($linkedAccounts as $linkedAccount) {
                $linkedAccount->master_account_id = null;
                $linkedAccount->save();
            }

            DB::commit();
        } catch (Exception $ex) {
            DB::rollBack();
            throw $ex;
        }

        $localPath = $this->_storageHelper->getAvatarPath($accountId);
        if (file_exists($localPath)) {
            unlink($localPath);
        }

        event(new AccountDestroyed($account, $accountName, $this->_authManager->user()->id));
    }

    public function getNextAvailableNickname(?string $nickname = null): string
    {
        if ($nickname === null || empty($nickname)) {
            $nickname = config('ed.default_account_name');
        }

        // reduce maximum length to accomodate for space and numbering,
        // in the event that a user with the same nickname already exists.
        $maxLength = config('ed.max_nickname_length') - 4;
        if (mb_strlen($nickname) > $maxLength) {
            $nickname = mb_substr($nickname, 0, $maxLength);
        }

        $i = 1;
        $tmp = $nickname;

        do {
            if (Account::where('nickname', '=', $tmp)->count() < 1) {
                return $tmp;
            }

            $tmp = $nickname.' '.$i;
            $i = $i + 1;
        } while (true);
    }
}
