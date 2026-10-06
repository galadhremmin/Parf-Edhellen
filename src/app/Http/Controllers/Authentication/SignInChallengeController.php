<?php

namespace App\Http\Controllers\Authentication;

use App\Events\AccountSecurityActivity;
use App\Events\AccountSecurityActivityResultEnum;
use App\Repositories\SystemErrorRepository;
use App\Security\AccountManager;
use App\Security\ChallengeOutcome;
use App\Security\SignInChallenge;
use Illuminate\Http\Request;

/**
 * The page that asks for the code e-mailed by `SignInChallenge`.
 */
class SignInChallengeController extends AuthenticationController
{
    public function __construct(
        SystemErrorRepository $systemErrorRepository,
        AccountManager $accountManager,
        protected readonly SignInChallenge $_signInChallenge,
    ) {
        parent::__construct($systemErrorRepository, $accountManager);
    }

    public function show(Request $request)
    {
        $account = $this->_signInChallenge->pendingAccount($request);
        if ($account === null) {
            return redirect()->route('login');
        }

        return view('authentication.confirm-sign-in', [
            'account' => $account,
            'maskedEmail' => self::maskEmail($account->email),
            'providerName' => $account->authorization_provider?->name,
            'lifetime' => SignInChallenge::LIFETIME_MINUTES,
        ]);
    }

    public function confirm(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:16'],
        ]);

        $account = $this->_signInChallenge->pendingAccount($request);
        $remember = $this->_signInChallenge->remember($request);
        $outcome = $this->_signInChallenge->verify($request, $data['code']);

        if ($outcome === ChallengeOutcome::Passed && $account === null) {
            $outcome = ChallengeOutcome::Expired;
        }

        if ($outcome !== ChallengeOutcome::Passed) {
            if ($account !== null) {
                event(AccountSecurityActivity::fromRequest($request, $account->master_account ?? $account, 'login-challenge',
                    AccountSecurityActivityResultEnum::FAILURE, null, $account));
            }

            return match ($outcome) {
                ChallengeOutcome::Wrong => back()->withErrors(['code' => 'That code isn\'t right. Check the e-mail and try again.']),
                ChallengeOutcome::Exhausted => redirect()->route('login')->with('error', 'Too many wrong codes. Sign in again to get a new one.'),
                default => redirect()->route('login')->with('error', 'Your sign-in code has expired. Sign in again to get a new one.'),
            };
        }

        $account->refresh();
        event(AccountSecurityActivity::fromRequest($request, $account->master_account ?? $account, 'login',
            AccountSecurityActivityResultEnum::SUCCESS, null, $account));

        return $this->doLogin($request, $account, false, $remember);
    }

    public function resend(Request $request)
    {
        $account = $this->_signInChallenge->pendingAccount($request);
        if ($account === null) {
            return redirect()->route('login');
        }

        $this->_signInChallenge->issue($request, $account, $this->_signInChallenge->remember($request));

        return redirect()->route('auth.confirm-sign-in')->with('status', 'We\'ve sent you a new code.');
    }

    /**
     * `jane.doe@example.com` -> `j•••@example.com`: enough to recognise, not enough to harvest.
     */
    private static function maskEmail(?string $email): string
    {
        if (empty($email) || ! str_contains($email, '@')) {
            return 'your e-mail address';
        }

        [$local, $domain] = explode('@', $email, 2);

        return mb_substr($local, 0, 1).'•••@'.$domain;
    }
}
