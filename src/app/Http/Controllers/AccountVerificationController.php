<?php

namespace App\Http\Controllers;

use App\Events\EmailVerificationSent;
use App\Http\Controllers\Abstracts\Controller;
use App\Repositories\SystemErrorRepository;
use App\Security\ChallengeOutcome;
use App\Security\EmailVerification;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * The "confirm your e-mail address" interstitial that holds a new (or never verified) account until
 * the address is proven, by the code or the link in the e-mail it sends.
 */
class AccountVerificationController extends Controller
{
    public function __construct(
        protected readonly SystemErrorRepository $_systemErrorRepository,
        protected readonly EmailVerification $_emailVerification,
    ) {}

    public function verificationNotice(Request $request)
    {
        $user = $request->user();
        if ($user->hasVerifiedEmail()) {
            return $this->welcome();
        }

        // Arriving without a code in hand: send one, so the e-mail is already on its way.
        if (! $this->_emailVerification->isPending($request, $user)) {
            $this->send($request);
        }

        return view('account.verification-required', [
            'user' => $user,
        ]);
    }

    public function verifyAccount(Request $request)
    {
        $sent = $this->send($request);

        return redirect()->route('verification.notice')->with('status', $sent
            ? 'We\'ve sent you a new e-mail.'
            : 'We sent you one a moment ago. Give it a minute, and check your spam folder too.');
    }

    public function checkCode(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:16'],
        ]);

        $outcome = $this->_emailVerification->verifyCode($request, $request->user(), $data['code']);

        return match ($outcome) {
            ChallengeOutcome::Passed => $this->welcome(),
            ChallengeOutcome::Wrong => back()->withErrors(['code' => 'That code isn\'t right. Check the e-mail and try again.']),
            default => redirect()->route('verification.notice')->withErrors([
                'code' => 'That code can no longer be used. Ask for a new e-mail below.',
            ]),
        };
    }

    public function confirmVerificationFromEmail(EmailVerificationRequest $request): RedirectResponse
    {
        $this->_emailVerification->markVerified($request->user());

        return $this->welcome();
    }

    /**
     * Wherever they were heading when we stopped them, or their profile.
     */
    private function welcome(): RedirectResponse
    {
        return redirect()->intended(route('author.my-profile'));
    }

    private function send(Request $request): bool
    {
        try {
            $sent = $this->_emailVerification->send($request, $request->user());
            if ($sent) {
                event(new EmailVerificationSent($request->user()));
            }

            return $sent;
        } catch (\Exception $ex) {
            $this->_systemErrorRepository->saveException($ex);

            return false;
        }
    }
}
