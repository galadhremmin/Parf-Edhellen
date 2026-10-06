<?php

namespace App\Http\Controllers\Authentication;

use App\Events\AccountSecurityActivity;
use App\Events\AccountSecurityActivityResultEnum;
use App\Exceptions\SuspiciousBotActivityException;
use App\Helpers\RecaptchaHelper;
use App\Interfaces\IIdentityProvider;
use App\Models\Account;
use App\Models\AuthorizationProvider;
use App\Repositories\SystemErrorRepository;
use App\Security\AccountManager;
use App\Security\Identity\ProviderIdentity;
use App\Security\SignInChallenge;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OAuthAuthenticationController extends AuthenticationController
{
    private const RECAPTCHA_ASSESSMENT_RESULT_SESSION_KEY = 'recaptcha_assessment_result';

    public function __construct(
        SystemErrorRepository $systemErrorRepository,
        AccountManager $accountManager,
        protected readonly SignInChallenge $_signInChallenge,
    ) {
        parent::__construct($systemErrorRepository, $accountManager);
    }

    public function redirect(Request $request, string $providerName)
    {
        $assessmentResult = [];
        if (config('ed.recaptcha.sitekey') && ! RecaptchaHelper::createAssessment($request->query('recaptcha_token'), 'LOGIN', $assessmentResult)) {
            $this->log('redirect', $providerName, new SuspiciousBotActivityException($request, 'user login', $assessmentResult));

            return redirect()->route('login')->with('error', 'Recaptcha error - are you a bot?');
        }

        // This is unfortunate, but we need to store the assessment result in the session so that it can be used in the callback.
        $request->session()->put(
            self::RECAPTCHA_ASSESSMENT_RESULT_SESSION_KEY, //
            json_encode($assessmentResult)
        );

        try {
            return $this->identityProviderFor(self::getProvider($providerName))->redirect($request);
        } catch (\Exception $ex) {
            $this->log('redirect', $providerName, $ex);

            return $this->redirectOnSystemError($providerName);
        }
    }

    public function callback(Request $request, string $providerName)
    {
        $assessmentResult = [];

        if ($request->session()->has(self::RECAPTCHA_ASSESSMENT_RESULT_SESSION_KEY)) {
            $assessmentResult = json_decode(
                $request->session()->get(self::RECAPTCHA_ASSESSMENT_RESULT_SESSION_KEY),
                true
            );
            $request->session()->forget(self::RECAPTCHA_ASSESSMENT_RESULT_SESSION_KEY);
        }

        $user = null;
        try {
            $provider = self::getProvider($providerName);
            $identity = $this->identityProviderFor($provider)->resolveIdentity($request);

            $subject = $identity->subject;
            $email = $identity->email;

            $user = Account::where([
                ['authorization_provider_id', '=', $provider->id],
                ['identity', '=', $subject],
            ])->first() ?? $this->_accountManager->claimLegacyIdentity($provider->id, $subject, $email);

            $first = false;
            if ($user === null) {
                if (empty($email)) {
                    return redirect()->route('login')->with('error',
                        'Your '.$provider->name.' account did not share an e-mail address. '.
                        'Please sign in with another method.');
                }

                $user = $this->_accountManager->createAccount(
                    $email,
                    $subject,
                    $provider->id,
                    null,
                    $identity->name
                );
                $this->acceptVouchedAddress($user, $identity);

                $first = true;
                event(AccountSecurityActivity::fromRequest($request, $user, 'registration', AccountSecurityActivityResultEnum::SUCCESS, $assessmentResult));
            } else {
                $this->acceptVouchedAddress($user, $identity);

                $authenticatedAs = $user;
                if ($user->master_account_id !== null) {
                    $user = $user->master_account;
                }

                // The provider vouches for the person, not for the address: prove the inbox first.
                if ($this->_signInChallenge->isRequiredFor($authenticatedAs)) {
                    $this->_signInChallenge->issue($request, $authenticatedAs, /* remember: */ true);
                    event(AccountSecurityActivity::fromRequest($request, $user, 'login-challenge', AccountSecurityActivityResultEnum::SUCCESS, $assessmentResult, $authenticatedAs));

                    return redirect()->route('auth.confirm-sign-in');
                }

                event(AccountSecurityActivity::fromRequest($request, $user, 'login', AccountSecurityActivityResultEnum::SUCCESS, $assessmentResult, $authenticatedAs));
            }

            return $this->doLogin($request, $user, $first, /* remember: */ true);
        } catch (\Exception $ex) {
            $this->log('callback', $providerName, $ex);

            if ($user !== null) {
                event(AccountSecurityActivity::fromRequest($request, $user, 'login', AccountSecurityActivityResultEnum::FAILURE, $assessmentResult));
            }

            return $this->redirectOnSystemError($providerName);
        }
    }

    /**
     * The provider's own word that the person controls the address counts as our verification, as
     * long as it's about the address we hold.
     */
    private function acceptVouchedAddress(Account $account, ProviderIdentity $identity): void
    {
        if ($account->email_verified_at === null && $identity->vouchesFor($account->email)) {
            $account->email_verified_at = Carbon::now();
            $account->save();
        }
    }

    private function identityProviderFor(AuthorizationProvider $provider): IIdentityProvider
    {
        return $this->_identityProviders->find($provider->name_identifier)
            ?? throw new \UnexpectedValueException('The identity provider "'.$provider->name_identifier.'" is not available.');
    }

    public static function getProvider(string $providerName)
    {
        if (empty($providerName)) {
            throw new \UnexpectedValueException('Missing an identity provider.');
        }

        $provider = AuthorizationProvider::where('name_identifier', $providerName)->first();
        if (! $provider) {
            throw new \UnexpectedValueException('The identity provider "'.$providerName.'" does not exist!');
        }

        return $provider;
    }
}
