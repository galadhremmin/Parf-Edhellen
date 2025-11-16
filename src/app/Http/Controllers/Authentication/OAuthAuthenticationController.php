<?php

namespace App\Http\Controllers\Authentication;

use App\Models\Account;
use App\Models\AuthorizationProvider;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite as FacadesSocialite;
use App\Helpers\RecaptchaHelper;
use App\Exceptions\SuspiciousBotActivityException;

class OAuthAuthenticationController extends AuthenticationController
{    
    public function redirect(Request $request, string $providerName)
    {
        if (config('ed.recaptcha.sitekey') && ! RecaptchaHelper::createAssessment($request->query('recaptcha_token'), 'LOGIN')) {
            $this->log('redirect', $providerName, new SuspiciousBotActivityException($request, 'user login'));
            return redirect()->route('login')->with('error', 'Recaptcha error - are you a bot?');
        }

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

            return $this->doLogin($request, $user, $first, /* remember: */ true);
        } catch (\Exception $ex) {
            $this->log('callback', $providerName, $ex);

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
