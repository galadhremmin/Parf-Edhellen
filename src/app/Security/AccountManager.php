<?php

namespace App\Security;

use App\Events\AccountAvatarChanged;
use App\Events\AccountChanged;
use App\Events\AccountPasswordChanged;
use App\Events\AccountsMerged;
use App\Helpers\StorageHelper;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use App\Models\Account;
use App\Models\AuthorizationProvider;
use App\Models\Role;
use Carbon\Carbon;
use Exception;
use Illuminate\Auth\Events\Registered;

class AccountManager
{
    /**
     * @var StorageHelper
     */
    private $_storageHelper;

    public function __construct(StorageHelper $storageHelper)
    {
        $this->_storageHelper = $storageHelper;
    }

    public function getRootAccount()
    {
        $role = Role::where('name', RoleConstants::Root)
            ->first();

        if ($role === null) {
            return null;
        }

        return $role->accounts()->first();
    }

    public function createAccount(string $username, string $identity = null, int $providerId = null, string $password = null, string $name = null)
    {
        $firstAccountThusAdmin = Account::count() === 0;
        $nickname = $firstAccountThusAdmin 
            ? 'Administrator' 
            : $this->getNextAvailableNickname($name);

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

        $user = Account::create([
            'email'             => $username,
            'identity'          => $identity,
            'nickname'          => $nickname,

            'authorization_provider_id' => $providerId,
            'is_passworded'             => ! empty($password),
            'is_master_account'         => ! empty($password),
            'password'                  => ! empty($password) ? Hash::make($password) : null
        ]);

        // Important!
        // The first user ever created is assumed to have been created by an administrator
        // of the website, and thus assigned the role Administrator.
        if ($firstAccountThusAdmin) {
            $user->addMembershipTo(RoleConstants::Administrators);
        }

        $user->addMembershipTo(RoleConstants::Users);

        // Notify Laravel that this user has been created. This will trigger downstream flows like
        // e-mail verification.
        event(new Registered($user));

        return $user;
    }

    public function createMasterAccount(Account $account)
    {
        if ($account->is_master_account) {
            throw new Exception('Attempting to create a master account for a master account. There can only be one master account per account.');
        }

        if ($this->getAccountByUsername($account->email) !== null) {
            throw new Exception(sprintf('A master account already exists for account %d.', $account->id));
        }

        $masterAccount = Account::create([
            'email'                     => $account->email,
            'nickname'                  => $account->nickname,
            'tengwar'                   => $account->tengwar,
            'profile'                   => $account->profile,
            'has_avatar'                => $account->has_avatar,
            'feature_background_url'    => $account->feature_background_url,
            'email_verified_at'         => $account->email_verified_at,
            'authorization_provider_id' => null,
            'master_account_id'         => null,
            'identity'                  => 'MASTER|'.$account->email,
            'is_master_account'         => 1,
            'is_passworded'             => 0
        ]);

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

    public function mergeAccounts(Collection $accounts)
    {
        if ($accounts->count() < 2) {
            return;
        }

        $masterAccount = Account::where('email', $accounts->first()->email)
            ->where('is_master_account', true)
            ->first();

        if ($masterAccount === null) {
            $masterAccount = $this->createMasterAccount($accounts->first());
        }
        
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
            throw new Exception('User is attempting to link a master account to itself.');
        }

        $account->nickname = str_replace('(linked)', '', trim($account->nickname)).' (linked)';
        $account->master_account_id = $masterAccount->id;
        $account->save();

        event(new AccountsMerged($masterAccount, collect([$account])));
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

    public function getAccountByUsername(string $username)
    {
        return Account::where('email', $username)
            ->where('is_master_account', true)
            ->first();
    }

    public function checkPasswordWithUsername(string $username, string $password)
    {
        $account = self::getAccountByUsername($username);
        if ($account === null) {
            return false;
        }

        return self::checkPasswordWithAccount($account, $password);
    }

    public function checkPasswordWithAccount(Account $account, string $password)
    {
        return Hash::check($password, $account->password);
    }

    public function delete(Account $account)
    {
        $uuid      = 'DELETED|'.Str::uuid();
        $date      = Carbon::now()->toDateTimeString();
        $accountId = $account->id;

        $account->is_deleted                 = true;
        $account->nickname                   = sprintf('(Deleted %s)', $date);
        $account->email                      = 'deleted@'.$uuid;
        $account->authorization_provider_id  = null;
        $account->identity                   = $uuid;
        $account->profile                    = 'The user deleted their account on '.$date;
        $account->tengwar                    = null;
        $account->has_avatar                 = 0;
        $account->is_master_account          = false;
        $account->is_passworded              = false;
        $account->master_account_id          = null;
        $account->password                   = null;
        $account->save();

        $linkedAccounts = Account::where('master_account_id', $accountId)
            ->get();

        foreach ($linkedAccounts as $linkedAccount) {
            $linkedAccount->master_account_id = null;
            $linkedAccount->save();
        }

        $localPath = $this->_storageHelper->getAvatarPath($accountId);
        if (file_exists($localPath)) {
            unlink($localPath);
        }
    }

    public function getNextAvailableNickname(string $nickname = null)
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

            $tmp = $nickname . ' ' . $i;
            $i = $i + 1;
        } while (true);
    }
}
