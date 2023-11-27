<?php

namespace App\Http\Controllers;

use App\Events\{
    AccountAvatarChanged,
    AccountsMerged
};
use App\Helpers\StorageHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Abstracts\Controller;
use App\Models\Account;
use App\Security\RoleConstants;
use Closure;
use Illuminate\Validation\ValidationException;

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
        $hasMerged = $request->query('merged', 0) !== 0;

        return view('account.privacy', [
            'user' => $user,
            'accounts' => $accounts,
            'hasMerged' => $hasMerged
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
            $masterAccount = Account::create([
                'email' => $account->email,
                'nickname' => $account->nickname,
                'tengwar' => $account->tengwar,
                'profile' => $account->profile,
                'has_avatar' => $account->has_avatar,
                'feature_background_url' => $account->feature_background_url,
                'authorization_provider_id' => null,
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
        }

        $linkedAccounts = Account::whereIn('id', $requestData['account_id'])->get();
        foreach ($linkedAccounts as $linkedAccount) {
            $linkedAccount->master_account_id = $masterAccount->id;
            $linkedAccount->save();
        }

        event(new AccountsMerged($masterAccount, $linkedAccounts));
        auth()->login($masterAccount);

        return redirect()->route('account.privacy', ['merged' => $masterAccount->id]);
    }
}
