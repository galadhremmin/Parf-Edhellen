<?php

namespace App\Http\Controllers\Authentication;

use App\Models\Account;
use App\Models\AuthorizationProvider;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite as FacadesSocialite;
use App\Helpers\RecaptchaHelper;
use App\Exceptions\SuspiciousBotActivityException;
use App\Events\AccountSecurityActivity;
use App\Events\AccountSecurityActivityResultEnum;

class OAuthAuthenticationController extends AuthenticationController
{    
    private const RECAPTCHA_ASSESSMENT_RESULT_SESSION_KEY = 'recaptcha_assessment_result';

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
            $provider = self::getProvider($providerName);

            return FacadesSocialite::driver($provider->name_identifier)->redirect();
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
            $providerUser = FacadesSocialite::driver($provider->name_identifier)->user();

            $user = Account::where([
                ['email', '=', $providerUser->getEmail()],
                ['authorization_provider_id', '=', $provider->id],
            ])->first();

            $first = false;
            if ($user === null) {
                $user = $this->_accountManager->createAccount(
                    $providerUser->getEmail(),
                    $providerUser->getId(),
                    $provider->id,
                    null,
                    $providerUser->getName()
                );

                $first = true;
            }

            if ($first) {
                event(AccountSecurityActivity::fromRequest($request, $user, 'registration', AccountSecurityActivityResultEnum::SUCCESS, $assessmentResult));
            } else {
                event(AccountSecurityActivity::fromRequest($request, $user, 'login', AccountSecurityActivityResultEnum::SUCCESS, $assessmentResult));
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
