<?php

namespace App\Security;

use App\Mail\SignInCodeMail;
use App\Models\Account;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

/**
 * Verify on first use. A linked account that never verified its e-mail address must not open its
 * principal account on the strength of its identity provider alone: anyone can sign up with a
 * provider that doesn't check addresses. Instead, we e-mail a code to the address, and the person
 * must enter it in the same browser. That proves control of the inbox, after which the account
 * counts as verified and signs in normally.
 *
 * A code rather than a link: a link could be opened by the inbox owner in answer to someone
 * else's sign-in, verifying the stranger's account. A code only works in the session that asked.
 */
class SignInChallenge
{
    private const SESSION_KEY = 'auth.sign-in-challenge';

    public const LIFETIME_MINUTES = 10;

    public const MAX_ATTEMPTS = 5;

    public function isRequiredFor(Account $account): bool
    {
        return $account->master_account_id !== null && $account->email_verified_at === null;
    }

    /**
     * E-mails a new code for `$account` and remembers it in this session, replacing any earlier one.
     */
    public function issue(Request $request, Account $account, bool $remember): void
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $request->session()->put(self::SESSION_KEY, [
            'account_id' => $account->id,
            'code_hash' => Hash::make($code),
            'expires_at' => Carbon::now()->addMinutes(self::LIFETIME_MINUTES)->getTimestamp(),
            'attempts' => 0,
            'remember' => $remember,
        ]);

        Mail::to($account->email)->queue(new SignInCodeMail(
            $code,
            $account->nickname,
            $account->authorization_provider?->name ?? 'another sign-in method',
        ));
    }

    /**
     * The account waiting for a code in this session, if the challenge is still open.
     */
    public function pendingAccount(Request $request): ?Account
    {
        $challenge = $request->session()->get(self::SESSION_KEY);
        if (! is_array($challenge) || $challenge['expires_at'] < Carbon::now()->getTimestamp()) {
            return null;
        }

        return Account::find($challenge['account_id']);
    }

    public function remember(Request $request): bool
    {
        return (bool) ($request->session()->get(self::SESSION_KEY)['remember'] ?? false);
    }

    /**
     * Checks the code. On success the account is marked verified and the challenge closed; too many
     * wrong codes close it as well, so the person has to sign in again for a new one.
     */
    public function verify(Request $request, string $code): ChallengeOutcome
    {
        $challenge = $request->session()->get(self::SESSION_KEY);
        if (! is_array($challenge) || $challenge['expires_at'] < Carbon::now()->getTimestamp()) {
            $request->session()->forget(self::SESSION_KEY);

            return ChallengeOutcome::Expired;
        }

        if (! Hash::check(preg_replace('/\s+/', '', $code), $challenge['code_hash'])) {
            $challenge['attempts'] += 1;
            if ($challenge['attempts'] >= self::MAX_ATTEMPTS) {
                $request->session()->forget(self::SESSION_KEY);

                return ChallengeOutcome::Exhausted;
            }

            $request->session()->put(self::SESSION_KEY, $challenge);

            return ChallengeOutcome::Wrong;
        }

        $request->session()->forget(self::SESSION_KEY);

        $account = Account::find($challenge['account_id']);
        if ($account === null) {
            return ChallengeOutcome::Expired;
        }

        $account->email_verified_at = Carbon::now();
        $account->save();

        return ChallengeOutcome::Passed;
    }
}
