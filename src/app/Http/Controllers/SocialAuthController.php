<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Socialite;
use Carbon\Carbon;

use App\Models\{ Account, AuthorizationProvider, AuditTrail };
use App\Repositories\Interfaces\IAuditTrailRepository;

class SocialAuthController extends Controller
{
    protected $_auditTrail;

    public function __construct(IAuditTrailRepository $auditTrail) 
    {
        $this->_auditTrail = $auditTrail;
    }

    public function login(Request $request)
    {
        if ($request->has('redirect')) {
            $url = parse_url($request->input('redirect'));

            if ($url !== false && isset($url['path'])) {
                $path = $url['path'];

                if (isset($url['query'])) {
                    $path .= $url['query'];
                }

                if (isset($url['fragment'])) {
                    $path .= '#'.$url['fragment'];
                }

                $request->session()->put('auth.redirect', $path);
            }
        }

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
            $nickname = self::getNextAvailableNickname($providerUser->getName() ?: 'Account');

            $user = Account::create([
                'email'          => $providerUser->getEmail(),
                'identity'       => $providerUser->getId(),
                'nickname'       => $nickname,
                'is_configured'  => 0,
                
                'authorization_provider_id'  => $provider->id
            ]);

            // Register an audit trail for the user logging in for the first time.
            $this->_auditTrail->store(AuditTrail::ACTION_PROFILE_FIRST_TIME, $user, $user->id);
        } else {
            // Register an audit trail for the user logging in.
            $this->_auditTrail->store(AuditTrail::ACTION_PROFILE_AUTHENTICATED, $user, $user->id);
        }

        auth()->login($user);

        if ($request->session()->has('auth.redirect')) {
            $path = $request->session()->pull('auth.redirect');
            return redirect($path);
        }
        
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
