<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\AuthProvider;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Socialite;

class SocialAuthController extends Controller
{
    public function login()
    {
        $providers = AuthProvider::all();
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
        return Socialite::driver($provider->URL)->redirect();   
    }   

    public function callback(Request $request, string $providerName)
    {
        $provider = self::getProvider($providerName);
        $providerUser = Socialite::driver($provider->URL)->user(); 

        $user = User::where([
                [ 'Email', '=', $providerUser->getEmail() ],
                [ 'ProviderID', '=', $provider->ProviderID ]
            ])->first();

        if (! $user) {
            $nickname = self::getNextAvailableNickname($providerUser->getName());

            $user = User::create([
                'Email'          => $providerUser->getEmail(),
                'Identity'       => $providerUser->getId(),
                'Nickname'       => $nickname,
                
                'DateRegistered' => Carbon::now(),
                'ProviderID'     => $provider->ProviderID,
                'Configured'     => 0
            ]);
        }

        auth()->login($user);
        return redirect()->route('dashboard', [ 'loggedIn' => true ]);
    }

    public static function getProvider(string $providerName) {
        if (empty($providerName)) {
            throw new \UnexpectedValueException('Missing an identity provider.');
        }

        $provider = AuthProvider::where('Name', '=', $providerName)->first();
        if (! $provider) {
            throw new \UnexpectedValueException('The identity provider "' . $providerName . '" does not exist!');
        }

        return $provider;
    }

    private static function getNextAvailableNickname(string $nickname) {
        $i = 1;
        $tmp = $nickname;

        do {
            if (User::where('Nickname', '=', $tmp)->count() < 1) {
                return $tmp;
            }

            $tmp = $nickname . ' ' . $i;
            $i = $i + 1;
        } while (true);
    }
}
