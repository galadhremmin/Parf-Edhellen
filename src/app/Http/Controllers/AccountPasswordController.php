<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Abstracts\Controller;
use App\Security\AccountManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AccountPasswordController extends Controller
{
    private AccountManager $_accountManager;

    public function __construct(AccountManager $passwordManager)
    {
        $this->_accountManager = $passwordManager;
    }

    public function createPassword(Request $request)
    {
        $account = $request->user();
        $data = Validator::make($request->all(), [
            'new-password' => [
                'required',
                'confirmed',
                Password::defaults(),
                function (string $attribute, mixed $value, Closure $fail) use ($account) {
                    if ($account->master_account_id !== null) {
                        $fail('You cannot create a password to your linked account. Sign in to your principal account first.');
                    } elseif (! $account->is_passworded && $account->email_verified_at === null) {
                        $fail('Verify your e-mail address before you create a password.');
                    } elseif (! $account->is_master_account && $this->_accountManager->getUnverifiedMasterAccountFor($account) !== null) {
                        $fail('An unverified account with a password already uses your e-mail address. Verify or release that account under Accounts above.');
                    } elseif (! $account->is_master_account && $this->_accountManager->getVerifiedMasterAccountByEmail($account->email) !== null) {
                        $fail('A principal account already uses your e-mail address. Link this account to it under Accounts above.');
                    }
                },
            ],
            'existing-password' => [
                Rule::requiredIf((bool) $account->is_passworded),
                'nullable',
                'string',
                function (string $attribute, mixed $value, Closure $fail) use ($account) {
                    if ($account->is_passworded && ! $this->_accountManager->checkPasswordWithAccount($account, $value)) {
                        $fail('Incorrect current password. Please try again.');
                    }
                },
            ],
        ])->validate();

        $password = $data['new-password'];
        $isMasterAccount = $account->is_master_account;

        $account = $this->_accountManager->updatePassword($account, $password);
        auth()->login($account);

        return redirect()->route('account.security', [
            'passworded' => $account->id,
            'new-account' => ! $isMasterAccount,
        ]);
    }
}
