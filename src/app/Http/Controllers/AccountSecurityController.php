<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Abstracts\Controller;
use App\Models\Account;
use App\Models\AccountMergeRequest;
use App\Security\AccountManager;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AccountSecurityController extends Controller
{
    public function __construct(
        protected readonly AccountManager $_accountManager,
    ) {}

    /**
     * Landing page for account security settings and account linking.
     */
    public function security(Request $request)
    {
        $user = $request->user();

        $accounts = Account::with('authorization_provider') //
            ->withCount(['lexical_entries', 'sentences', 'contributions', 'forum_posts'])
            ->where('email', $user->email) //
            ->orWhere('master_account_id', $user->id)
            ->get();

        // What each account has done, so people recognise their own before linking it.
        $accountActivity = $accounts->mapWithKeys(fn (Account $account) => [
            $account->id => self::describeActivity($account),
        ]);

        $isMerged = intval($request->query('merged', 0)) !== 0;
        $isPassworded = intval($request->query('passworded', 0)) !== 0;
        $isNewAccount = boolval($request->query('new-account', false)) === true;
        $verificationStatus = $request->query('verification', null);
        $isReleased = intval($request->query('released', 0)) !== 0;

        // Only a verified user may see, and release, another account on their address.
        $unverifiedHolder = $user->email_verified_at !== null
            ? $this->_accountManager->getUnverifiedMasterAccountFor($user)
            : null;

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
            'account_activity' => $accountActivity,
            'is_released' => $isReleased,
            'unverified_holder' => $unverifiedHolder,
        ]);
    }

    private static function describeActivity(Account $account): string
    {
        $parts = collect([
            'word' => $account->lexical_entries_count,
            'phrase' => $account->sentences_count,
            'contribution' => $account->contributions_count,
            'post' => $account->forum_posts_count,
        ])
            ->filter(fn (int $count) => $count > 0)
            ->map(fn (int $count, string $noun) => $count.' '.Str::plural($noun, $count));

        return $parts->isEmpty() ? 'No activity' : $parts->join(' · ');
    }
}
