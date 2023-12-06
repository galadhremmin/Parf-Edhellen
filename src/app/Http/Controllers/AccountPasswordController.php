<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{
    Validator
};
use Illuminate\Validation\Rules\Password;
use App\Http\Controllers\Abstracts\Controller;
use App\Security\AccountManager;
use Closure;

class AccountPasswordController extends Controller
{
    /**
     * @var AccountManager
     */
    private $_accountManager;

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
                    }
                }
            ],
            'existing-password' => [
                'required',
                'string',
                function (string $attribute, mixed $value, Closure $fail) use ($account) {
                    if ($account->is_passworded && ! $this->_accountManager->checkPasswordWithAccount($account, $value)) {
                        $fail('Incorrect current password. Please try again.');
                    }
                }
            ]
        ])->validate();

        $password = $data['new-password'];
        $isMasterAccount = $account->is_master_account;
       
        $account = $this->_accountManager->updatePassword($account, $password);
        auth()->login($account);

        return redirect()->route('account.security', [
            'passworded' => $account->id,
            'new-account' => ! $isMasterAccount
        ]);
    }
}
