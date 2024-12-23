<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Abstracts\Controller;
use App\Mail\AccountMergeMail;
use App\Models\Account;
use App\Models\AccountMergeRequest;
use App\Security\AccountManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AccountMergeController extends Controller
{
    private AccountManager $_accountManager;

    public function __construct(AccountManager $passwordManager)
    {
        $this->_accountManager = $passwordManager;
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

                    if (! $account->email_verified_at === null) {
                        $fail('Verify your e-mail address before creating a linking request.');
                    }
                },
            ],
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
            'id' => $requestId,
            'account_id' => $account->id,
            'account_ids' => json_encode($accountIds),

            // The client needs to provide the token via their e-mail inbox to activate the merge.
            'verification_token' => $token,

            // Save some basic information about who is making the request
            'requester_account_id' => $account->id,
            'requester_ip' => $request->ip(),
        ]);

        $providers = Account::whereIn('id', $accountIds)
            ->get()
            ->map(function ($account) {
                $provider = $account->authorization_provider()->withTrashed()->first();

                return $provider !== null ? $provider->name : null;
            })
            ->filter(function ($providerName) {
                return $providerName !== null;
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
            'id' => $requestId,
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
            'accounts' => $accounts,
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
                        'is_fulfilled' => false,
                    ])->first();
                    if ($request === null) {
                        $fail('Either your verification token is incorrect or there is no outstanding linking request for your account.');
                    }

                    if (! $account->email_verified_at === null) {
                        $fail('Verify your e-mail address before creating a linking request.');
                    }
                },
            ],
        ]);

        $data = $validator->validate();
        $request = AccountMergeRequest::where([
            'account_id' => $account->id,
            'verification_token' => $data['token'],
            'is_fulfilled' => false,
        ])->firstOrFail();

        $error = null;
        try {
            $accountIds = collect(json_decode($request->account_ids))->merge([$request->id]);
            $accounts = Account::whereIn('id', $accountIds)->get();
            $masterAccount = $this->_accountManager->mergeAccounts($accounts);
            auth()->login($masterAccount);
        } catch (\Exception $ex) {
            $error = $ex->getMessage()."\n\n".$ex->getTraceAsString();
        } finally {
            $request->is_fulfilled = empty($error);
            $request->is_error = ! $request->is_fulfilled;
            $request->error = $error;
            $request->save();
        }

        return redirect()->route('account.merge-status', ['requestId' => $request->id]);
    }
}
