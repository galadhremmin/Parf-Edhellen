<?php

namespace App\Security;

use App\Mail\SignInCodeMail;
use App\Models\Account;
use Carbon\Carbon;
use Illuminate\Http\Request;
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
    public const LIFETIME_MINUTES = InboxCode::LIFETIME_MINUTES;

    public const MAX_ATTEMPTS = InboxCode::MAX_ATTEMPTS;

    private InboxCode $_code;

    public function __construct()
    {
        $this->_code = new InboxCode('sign-in');
    }

    public function isRequiredFor(Account $account): bool
    {
        return $account->master_account_id !== null && $account->email_verified_at === null;
    }

    /**
     * E-mails a new code for `$account` and remembers it in this session, replacing any earlier one.
     */
    public function issue(Request $request, Account $account, bool $remember): void
    {
        $code = $this->_code->issue($request, $account->id, ['remember' => $remember]);

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
        $pending = $this->_code->pending($request);

        return $pending === null ? null : Account::find($pending['account_id']);
    }

    public function remember(Request $request): bool
    {
        return (bool) ($this->_code->pending($request)['context']['remember'] ?? false);
    }

    /**
     * Checks the code. On success the account is marked verified and the challenge closed; too many
     * wrong codes close it as well, so the person has to sign in again for a new one.
     */
    public function verify(Request $request, string $code): ChallengeOutcome
    {
        $pending = $this->_code->pending($request);
        $outcome = $this->_code->check($request, $code);
        if ($outcome !== ChallengeOutcome::Passed) {
            return $outcome;
        }

        $account = Account::find($pending['account_id'] ?? 0);
        if ($account === null) {
            return ChallengeOutcome::Expired;
        }

        $account->email_verified_at = Carbon::now();
        $account->save();

        return ChallengeOutcome::Passed;
    }
}
