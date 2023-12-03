<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{
    Hash,
    Mail,
    Validator
};
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Str;
use App\Http\Controllers\Abstracts\Controller;
use App\Mail\AccountMergeMail;
use App\Models\Account;
use App\Models\AccountMergeRequest;
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

        $isMerged     = intval($request->query('merged', 0)) !== 0;
        $isPassworded = intval($request->query('passworded', 0)) !== 0;
        $isNewAccount = boolval($request->query('new-account', false)) === true; 

        $numberOfAccounts = $accounts->filter(function ($account) {
            return $account->master_account_id === null && //
                ! $account->is_master_account;
        })->count();

        $mergeRequests = AccountMergeRequest::where([
            'account_id' => $user->id
        ])->get();
 
        // create a map of request id to to-be-linked accounts. The map will be used in the table that lists ongoing
        // linking requests.
        $mergeRequestAccounts = $mergeRequests->reduce(function(array $carry, AccountMergeRequest $request) {
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
            'merge_request_accounts' => $mergeRequestAccounts
        ]);
    }

    public function merge(Request $request)
    {
        $account = $request->user();
        $validator = Validator::make($request->all(), [
            'account_id' => [
                'required', 
                'array',
                function (string $attribute, mixed $values, Closure $fail) use ($account) {
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

                    $mergeRequests = AccountMergeRequest::where('account_id', $account->id)
                        ->where('is_fulfilled', '<>', 1);
                    if ($mergeRequests->count() > 0) {
                        $fail('Complete existing linking requests before creating a new one.');
                    }

                    if (! $account->is_configured) {
                        $fail('Verify your e-mail address before creating a linking request.');
                    }
                }
            ]
        ]);

        $data = $validator->validate();

        $accountIds = collect($data['account_id'])
            ->map(function ($id) {
                return intval($id);
            })
            ->values();

        $token = Hash::make(
            $account->id.'|'.
            time().'|'.
            Str::random(40)
        );

        $requestId = Str::uuid();
        $request = AccountMergeRequest::create([
            'id'          => $requestId,
            'account_id'  => $account->id,
            'account_ids' => json_encode($accountIds),

            // The client needs to provide the token via their e-mail inbox to activate the merge.
            'verification_token' => $token,

            // Save some basic information about who is making the request
            'requester_account_id' => $account->id,
            'requester_ip' => $_SERVER['REMOTE_ADDR']
        ]);

        $providers = Account::whereIn('id', $accountIds)
            ->get()
            ->filter(function ($account) {
                return $account->authorization_provider !== null;
            })
            ->map(function ($account) {
                return $account->authorization_provider->name;
            })
            ->join(', ');

        $mail = new AccountMergeMail($requestId, $providers, $token);
        Mail::to($account->email)->queue($mail);

        return redirect()->route('account.merge-status', ['requestId' => $requestId]);
    }

    public function mergeStatus(Request $request, string $requestId)
    {
        $account = $request->user();

        $mergeRequest = AccountMergeRequest::where([
            'id' => $requestId
        ])->firstOrFail();

        if ($mergeRequest->account_id !== $account->id &&
            in_array($account->id, json_decode($mergeRequest->account_ids))) {
            return response(null, 404);
        }

        $accounts = collect(json_decode($mergeRequest->account_ids))->map(function ($id) {
            return Account::findOrFail(intval($id));
        });

        return view('account.merge-status', [
            'mergeRequest' => $mergeRequest,
            'accounts'     => $accounts
        ]);
    }

    public function cancelMerge(Request $request, string $requestId)
    {
        $account = $request->user();

        $mergeRequest = AccountMergeRequest::where([
            'id' => $requestId,
            'account_id' => $account->id,
        ])->firstOrFail();

        if (! $mergeRequest->is_fulfilled && ! $mergeRequest->is_error) {
            $mergeRequest->delete();
        }

        return redirect()->route('account.security');
    }

    public function confirmMerge(Request $request)
    {
        $account = $request->user();
        $validator = Validator::make($request->all(), [
            'token' => [
                'required',
                'string',
                function (string $attribute, mixed $value, Closure $fail) use ($account) {
                    $request = AccountMergeRequest::where([
                        'account_id' => $account->id,
                        'verification_token' => $value,
                        'is_fulfilled' => false
                    ])->first();
                    if ($request === null) {
                        $fail('Either your verification token is incorrect or there is no outstanding linking request for your account.');
                    }

                    if (! $account->is_configured) {
                        $fail('Verify your e-mail address before creating a linking request.');
                    }
                }
            ]
        ]);

        $data = $validator->validate();
        $request = AccountMergeRequest::where([
            'account_id' => $account->id,
            'verification_token' => $data['token'],
            'is_fulfilled' => false
        ])->firstOrFail();

        $error = null;
        try {
            $accountIds = collect(json_decode($request->account_ids))->merge([$request->id]);
            $accounts = Account::whereIn('id', $accountIds)->get();
            $masterAccount = $this->_accountManager->mergeAccounts($accounts);

            // Since linking requires the user to have approved the request via e-mail, we can here
            // safely say that the account has been verified (i.e. configured). This removes the
            // unnecessary step for the account holder to verify their account.
            $masterAccount->is_configured = true;
            $masterAccount->save();

            auth()->login($masterAccount);
        } catch (\Exception $ex) {
            $error = $ex->getMessage()."\n\n".$ex->getTraceAsString();
        } finally {
            $request->is_fulfilled = empty($error);
            $request->is_error = ! $request->is_fulfilled;
            $request->error = $error;
            $request->save();
        }

        return redirect()->route('account.merge-status', [ 'requestId' => $request->id ]);
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
