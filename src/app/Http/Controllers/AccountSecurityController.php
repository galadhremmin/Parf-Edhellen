<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Abstracts\Controller;
use App\Models\Account;
use App\Models\AccountMergeRequest;
use Illuminate\Http\Request;

class AccountSecurityController extends Controller
{
    /**
     * Landing page for account security settings and account linking.
     */
    public function security(Request $request)
    {
        $user = $request->user();

        $accounts = Account::with('authorization_provider') //
            ->where('email', $user->email) //
            ->orWhere('master_account_id', $user->id)
            ->get();

        $isMerged = intval($request->query('merged', 0)) !== 0;
        $isPassworded = intval($request->query('passworded', 0)) !== 0;
        $isNewAccount = boolval($request->query('new-account', false)) === true;
        $verificationStatus = $request->query('verification', null);

        $numberOfAccounts = $accounts->filter(function ($account) {
            return $account->master_account_id === null && //
                ! $account->is_master_account;
        })->count();

        $mergeRequests = AccountMergeRequest::where([
            'account_id' => $user->id,
        ])->get();

        // create a map of request id to to-be-linked accounts. The map will be used in the table that lists ongoing
        // linking requests.
        $mergeRequestAccounts = $mergeRequests->reduce(function (array $carry, AccountMergeRequest $request) {
            $carry[$request->id] = collect(json_decode($request->account_ids))->map(function ($id) {
                return Account::findOrFail($id);
            });

            return $carry;
        }, []);

        return view('account.security', [
            'user' => $user,
            'accounts' => $accounts,
            'is_merged' => $isMerged,
            'is_passworded' => $isPassworded,
            'number_of_accounts' => $numberOfAccounts,
            'is_new_account' => $isNewAccount,
            'merge_requests' => $mergeRequests,
            'merge_request_accounts' => $mergeRequestAccounts,
            'verification_status' => $verificationStatus,
        ]);
    }

}
