<?php

namespace App\Http\Controllers;

use App\Events\{
    AccountAvatarChanged,
    AccountsMerged
};
use App\Helpers\StorageHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use App\Http\Controllers\Abstracts\Controller;
use App\Models\Account;
use Closure;
use Exception;
use Illuminate\Support\Facades\Hash;

class AccountPrivacyController extends Controller
{
    private $_storageHelper;

    public function __construct(StorageHelper $storageHelper)
    {
        $this->_storageHelper = $storageHelper;
    }

    public function privacy(Request $request)
    {
        $user = request()->user();

        $accounts = Account::with('authorization_provider') //
            ->where('email', $user->email) //
            ->get();

        $isMerged = $request->query('merged', 0) !== 0;
        $isPassworded = $request->query('passworded', 0) !== 0;

        $numberOfAccounts = $accounts->filter(function ($account) {
            return $account->master_account_id === null && //
                ! $account->is_master_account;
        })->count();

        return view('account.privacy', [
            'user' => $user,
            'accounts' => $accounts,
            'is_merged' => $isMerged,
            'is_passworded' => $isPassworded,
            'number_of_accounts' => $numberOfAccounts
        ]);
    }
    public function merge(Request $request)
    {
        $account = $request->user();

        $accountsValidator = Validator::make($request->all(), [
            'account_id' => [
                'required', 
                'array',
                function (string $attribute, mixed $values, Closure $fail) {
                    $invalidEntries = array_filter($values, function ($value) {
                        return ! is_numeric($value);
                    });

                    if (! empty($invalidEntries)) {
                        $fail('At least one of the accounts does not exist. Check your input.');
                    }

                    $accountIds = array_unique($values);
                    $accounts = Account::whereIn('id', $accountIds) //
                        ->where('is_master_account', '<>', 1) //
                        ->whereNull('master_account_id') //
                        ->distinct() //
                        ->get();
                    
                    if ($accounts->count() !== count($values)) {
                        $fail('Cannot merge the accounts you have selected. Have some of them already been linked?');
                    }
                }
            ]
        ]);

        $requestData = $accountsValidator->validate();
        
        $masterAccount = Account::where('email', $account->email)
            ->where('is_master_account', 1)
            ->first();

        if ($masterAccount === null) {
            $masterAccount = $this->createMasterAccount($account);
        }

        $linkedAccounts = Account::whereIn('id', $requestData['account_id'])->get();
        foreach ($linkedAccounts as $linkedAccount) {
            $this->linkAccountToMasterAccount($linkedAccount, $masterAccount);
        }

        auth()->login($masterAccount);
        return redirect()->route('account.privacy', ['merged' => $masterAccount->id]);
    }

    public function createPassword(Request $request)
    {
        $account = $request->user();
        $data = Validator::make($request->all(), [
            'new-password'      => [
                'required',
                'confirmed',
                Password::defaults(),
                function (string $attribute, mixed $value, Closure $fail) use ($account) {
                    if ($account->master_account_id !== null) {
                        $fail('You cannot create a password to your linked account. Log in to your principal account first.');
                    }
                }
            ],
            'existing-password' => [
                'sometimes',
                function (string $attribute, mixed $value, Closure $fail) use ($account) {
                    $hashedPassword = Hash::make($value);
                    if (! Hash::check($account->password, $hashedPassword)) {
                        $fail('Incorrect current password. Please try again.');
                    }
                }
            ]
        ])->validate();

        if (! $account->is_master_account) {
            $masterAccount = $this->createMasterAccount($account);
            $this->linkAccountToMasterAccount($account, $masterAccount);

            auth()->login($masterAccount);

            $account = $masterAccount;
        }

        $account->is_passworded = true;
        $account->password = Hash::make($data['new-password']);
        $account->save();

        return redirect()->route('account.privacy', ['passworded' => $account->id]);
    }

    private function createMasterAccount(Account $account)
    {
        $masterAccount = Account::create([
            'email' => $account->email,
            'nickname' => $account->nickname,
            'tengwar' => $account->tengwar,
            'profile' => $account->profile,
            'has_avatar' => $account->has_avatar,
            'feature_background_url' => $account->feature_background_url,
            'authorization_provider_id' => null,
            'master_account_id' => null,
            'identity' => 'MASTER|'.$account->email,
            'is_configured' => 0,
            'is_master_account' => 1,
            'is_passworded' => 0
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

        return $masterAccount;
    }

    private function linkAccountToMasterAccount(Account $account, Account $masterAccount)
    {
        if ($account->id === $masterAccount->id) {
            throw new Exception('User is attempting to link a master account to itself.');
        }

        $account->master_account_id = $masterAccount->id;
        $account->save();

        event(new AccountsMerged($masterAccount, collect([$account])));
    }
}
