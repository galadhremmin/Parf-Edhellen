<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{
    Auth,
    Session
};

use Socialite;

use App\Events\AccountAuthenticated;
use App\Http\Controllers\Abstracts\Controller;
use App\Repositories\SystemErrorRepository;
use App\Models\{ 
    Account, 
    AuthorizationProvider 
};

class SocialAuthController extends Controller
{
    /**
     * @var SystemErrorRepository
     */
    private $_systemErrorRepository;

    public function __construct(SystemErrorRepository $systemErrorRepository)
    {
        $this->_systemErrorRepository = $systemErrorRepository;
    }

    public function login(Request $request)
    {
        if (app()->environment() === 'local' && $request->has('login-as')) {
            $accountId = intval($request->input('login-as'));
            $account = Account::findOrFail($accountId);
            return $this->doLogin($request, $account, false);
        }

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

        $error = null;
        if ($request->has('error')) {
            $error = (object) [
                'provider' => $request->query('provider'),
                'session_id' => Session::getId()
            ];
        }

        $providers = AuthorizationProvider::orderBy('name')->get();
        return view('authentication.login', [
            'providers' => $providers,
            'error'     => $error
        ]);
    }

    public function logout(Request $request)
    {
        $account = $request->user();
        if ($account) {
            $account->forgetRoles();
        }

        Auth::logout();
        return redirect()->to('/');
    }

    public function redirect(Request $request, string $providerName)
    {
        try {
            $provider = self::getProvider($providerName);
            return Socialite::driver($provider->name_identifier)->redirect();
        } catch(\Exception $ex) {
            $this->log('redirect', $providerName, $ex);
            return $this->redirectOnSystemError($providerName);
        }
    }   

    public function callback(Request $request, string $providerName)
    {
        try {
            $provider = self::getProvider($providerName);
            $providerUser = Socialite::driver($provider->name_identifier)->user(); 
        } catch (\Exception $ex) {
            $this->log('callback', $providerName, $ex);
            return $this->redirectOnSystemError($providerName);
        }

        $user = Account::where([
                [ 'email', '=', $providerUser->getEmail() ],
                [ 'authorization_provider_id', '=', $provider->id ]
            ])->first();

        $first = false;
        if (! $user) {
            $firstAccountThusAdmin = Account::count() === 0;
            $nickname = $firstAccountThusAdmin 
                ? 'Administrator' 
                : self::getNextAvailableNickname($providerUser->getName());

            $user = Account::create([
                'email'          => $providerUser->getEmail(),
                'identity'       => $providerUser->getId(),
                'nickname'       => $nickname,
                'is_configured'  => 0,
                
                'authorization_provider_id'  => $provider->id
            ]);

            // Important!
            // The first user ever created is assumed to have been created by an administrator
            // of the website, and thus assigned the role Administrator.
            if ($firstAccountThusAdmin) {
                $user->addMembershipTo('Administrators');
            }

            $user->addMembershipTo('Users');

            $first = true;
        }

        return $this->doLogin($request, $user, $first);
    }

    private function redirectOnSystemError(string $providerName)
    {
        return redirect()->to(route('login', ['error' => $providerName]));
    }

    private function doLogin(Request $request, Account $user, bool $first = false)
    {
        auth()->login($user);

        event(new AccountAuthenticated($user, $first));

        if ($request->session()->has('auth.redirect')) {
            $path = $request->session()->pull('auth.redirect');
            return redirect($path);
        }
        
        return redirect()->route('dashboard', [ 'loggedIn' => true ]);
    }

    public static function getProvider(string $providerName)
    {
        if (empty($providerName)) {
            throw new \UnexpectedValueException('Missing an identity provider.');
        }

        $provider = AuthorizationProvider::where('name_identifier', $providerName)->first();
        if (! $provider) {
            throw new \UnexpectedValueException('The identity provider "' . $providerName . '" does not exist!');
        }

        return $provider;
    }

    public static function getNextAvailableNickname(string $nickname) 
    {
        if ($nickname === null || empty($nickname)) {
            $nickname = config('ed.default_account_name');
        }

        // reduce maximum length to accomodate for space and numbering,
        // in the event that a user with the same nickname already exists.
        $maxLength = config('ed.max_nickname_length') - 4;
        if (mb_strlen($nickname) > $maxLength) {
            $nickname = mb_substr($nickname, 0, $maxLength);
        }

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

    private function log(string $method, string $provider, \Throwable $ex)
    {
        $this->_systemErrorRepository->saveException($ex, $provider.'-auth-'.$method);
    }
}
