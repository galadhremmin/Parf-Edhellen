<?php

namespace App\Http\Controllers;

use App\Events\EmailVerificationSent;
use Illuminate\Http\Request;
use App\Http\Controllers\Abstracts\Controller;
use App\Repositories\SystemErrorRepository;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class AccountVerificationController extends Controller
{
    /**
     * @var SystemErrorRepository
     */
    private $_systemErrorRepository;

    public function __construct(SystemErrorRepository $systemErrorRepository)
    {
        $this->_systemErrorRepository = $systemErrorRepository;
    }

    public function verifyAccount(Request $request)
    {
        $user = $request->user();

        try {
            $user->sendEmailVerificationNotification();
            event(new EmailVerificationSent($user));
        } catch (\Exception $ex) {
            // suppress errors
            $this->_systemErrorRepository->saveException($ex);
        }

        return redirect()->route('account.security', [ 'verification' => 'sent' ]);
    }

    public function confirmVerificationFromEmail(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            // The user is already verified, so no further action will be necessary.
            return redirect()->intended(route('account.security'));
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect()->intended(route('account.security', [ 'verification' => 'ok' ]));
    }

    public function verificationNotice(Request $request)
    {
        return view('account.verification-required');
    }
}
