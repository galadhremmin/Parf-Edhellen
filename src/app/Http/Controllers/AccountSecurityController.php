<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use App\Http\Controllers\Abstracts\Controller;
use App\Models\Account;
use App\Security\AccountManager;
use Closure;

class AccountSecurityController extends Controller
{
    /**
     * @var AccountManager
     */
    private $_accountManager;

    public function __construct(AccountManager $passwordManager)
    {
        $this->_accountManager = $passwordManager;
    }

    public function security(Request $request)
    {
        $user = request()->user();

        $accounts = Account::with('authorization_provider') //
            ->where('email', $user->email) //
            ->get();

        $isMerged     = intval($request->query('merged', 0)) !== 0;
        $isPassworded = intval($request->query('passworded', 0)) !== 0;
        $isNewAccount = boolval($request->query('new-account', false)) === true; 

        $numberOfAccounts = $accounts->filter(function ($account) {
            return $account->master_account_id === null && //
                ! $account->is_master_account;
        })->count();

        return view('account.security', [
            'user' => $user,
            'accounts' => $accounts,
            'is_merged' => $isMerged,
            'is_passworded' => $isPassworded,
            'number_of_accounts' => $numberOfAccounts,
            'is_new_account' => $isNewAccount
        ]);
    }
    public function merge(Request $request)
    {
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

        $data = $accountsValidator->validate();

        $accountIds = collect($data['account_id'])
            ->map(function ($id) {
                return intval($id);
            })
            ->merge([$request->user()->id]);

        $accounts = Account::whereIn('id', $accountIds)->get();
        $masterAccount = $this->_accountManager->mergeAccounts($accounts);

        auth()->login($masterAccount);
        return redirect()->route('account.security', ['merged' => $masterAccount->id]);
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
