<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\AuthorizationProvider;
use App\Models\Account;
use Illuminate\Support\Facades\Auth;
use Socialite;

class SocialAuthController extends Controller
{
    public function login()
    {
        $providers = AuthorizationProvider::all();
        return view('authentication.login', [ 'providers' => $providers ]);
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->to('/');
    }

    public function redirect(Request $request, string $providerName)
    {
        $provider = self::getProvider($providerName);
        return Socialite::driver($provider->name_identifier)->redirect();   
    }   

    public function callback(Request $request, string $providerName)
    {
        $provider = self::getProvider($providerName);
        $providerUser = Socialite::driver($provider->name_identifier)->user(); 

        $user = Account::where([
                [ 'email', '=', $providerUser->getEmail() ],
                [ 'authorization_provider_id', '=', $provider->id ]
            ])->first();

        if (! $user) {
            $nickname = self::getNextAvailableNickname($providerUser->getName());

            $user = Account::create([
                'email'          => $providerUser->getEmail(),
                'identity'       => $providerUser->getId(),
                'nickname'       => $nickname,
                'is_configured'  => 0,
                
                'authorization_provider_id'  => $provider->id
            ]);
        }

        auth()->login($user);
        return redirect()->route('dashboard', [ 'loggedIn' => true ]);
    }

    public static function getProvider(string $providerName) {
        if (empty($providerName)) {
            throw new \UnexpectedValueException('Missing an identity provider.');
        }

        $provider = AuthorizationProvider::where('name_identifier', $providerName)->first();
        if (! $provider) {
            throw new \UnexpectedValueException('The identity provider "' . $providerName . '" does not exist!');
        }

        return $provider;
    }

    private static function getNextAvailableNickname(string $nickname) {
        $i = 1;
        $tmp = $nickname;

        do {
            if (Account::where('nickname', '=', $tmp)->count() < 1) {
                return $tmp;
            }

            $tmp = $nickname . ' ' . $i;
            $i = $i + 1;
        } while (true);
    }
}
