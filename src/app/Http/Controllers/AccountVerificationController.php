<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Abstracts\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class AccountVerificationController extends Controller
{
    public function verifyAccount(Request $request)
    {
        $request->user()->sendEmailVerificationNotification();
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
