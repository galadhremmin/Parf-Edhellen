<?php

namespace App\Security;

use App\Mail\VerifyEmailAddressMail;
use App\Models\Account;
use Carbon\Carbon;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;

/**
 * Proving a signed-in account's address. One e-mail carries both a code, to type into the page
 * that asked (handy when the mail is read on a phone), and a link, one tap when it's the same
 * browser. Both only work for the account they were sent to.
 */
class EmailVerification
{
    /** One e-mail per account per this many seconds, however often the page is loaded. */
    public const COOLDOWN_SECONDS = 60;

    private InboxCode $_code;

    public function __construct()
    {
        $this->_code = new InboxCode('verify-email');
    }

    /**
     * Sends a new code and link unless one went out moments ago. Returns whether an e-mail was sent.
     */
    public function send(Request $request, Account $account): bool
    {
        $key = 'verify-email:'.$account->id;
        if (RateLimiter::tooManyAttempts($key, 1)) {
            return false;
        }
        RateLimiter::hit($key, self::COOLDOWN_SECONDS);

        $code = $this->_code->issue($request, $account->id);
        $link = URL::temporarySignedRoute('verification.verify', Carbon::now()->addMinutes(60), [
            'id' => $account->getKey(),
            'hash' => sha1($account->getEmailForVerification()),
        ]);

        Mail::to($account->email)->queue(new VerifyEmailAddressMail($code, $link, $account->nickname));

        return true;
    }

    /**
     * Whether this session is waiting for a code for `$account`.
     */
    public function isPending(Request $request, Account $account): bool
    {
        return ($this->_code->pending($request)['account_id'] ?? null) === $account->id;
    }

    /**
     * Checks a typed code for the signed-in `$account`, marking it verified when it's right.
     */
    public function verifyCode(Request $request, Account $account, string $code): ChallengeOutcome
    {
        if (! $this->isPending($request, $account)) {
            return ChallengeOutcome::Expired;
        }

        $outcome = $this->_code->check($request, $code);
        if ($outcome === ChallengeOutcome::Passed) {
            $this->markVerified($account);
        }

        return $outcome;
    }

    public function markVerified(Account $account): void
    {
        if ($account->markEmailAsVerified()) {
            event(new Verified($account));
        }
    }
}
