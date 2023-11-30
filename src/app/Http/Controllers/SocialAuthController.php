<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{
    Auth,
    Session,
    Validator
};
use Illuminate\Validation\Rules\Password;

use Socialite;

use App\Events\AccountAuthenticated;
use App\Http\Controllers\Abstracts\Controller;
use App\Repositories\SystemErrorRepository;
use App\Models\{ 
    Account, 
    AuthorizationProvider 
};
use App\Security\AccountManager;
use App\Security\RoleConstants;
use Closure;
use Exception;

class SocialAuthController extends Controller
{
    /**
     * @var SystemErrorRepository
     */
    private $_systemErrorRepository;

    /**
     * @var AccountManager
     */
    private $_accountManager;

    public function __construct(SystemErrorRepository $systemErrorRepository, AccountManager $passwordManager)
    {
        $this->_systemErrorRepository = $systemErrorRepository;
        $this->_accountManager = $passwordManager;
    }

    public function login(Request $request, $isNew = false)
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
        return view($isNew ? 'authentication.register' : 'authentication.login', [
            'providers' => $providers,
            'error'     => $error
        ]);
    }

    public function register(Request $request)
    {
        return $this->login($request, true);
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

            $user = Account::where([
                    [ 'email', '=', $providerUser->getEmail() ],
                    [ 'authorization_provider_id', '=', $provider->id ]
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

            return $this->doLogin($request, $user, $first);
        } catch (\Exception $ex) {
            $this->log('callback', $providerName, $ex);
            return $this->redirectOnSystemError($providerName);
        }
    }

    public function loginWithPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => [ 'required', 'string' ],
            'password' => [
                'required',
                'string',
                function (string $attribute, mixed $value, Closure $fail) use ($request) {
                    if (! $this->_accountManager->checkPasswordWithUsername($request->input('username'), $value)) {
                        $fail('We did not find an account with that e-mail and password combination. Check your e-mail and password and try again.');
                    }
                }
            ]
        ]);

        $data = $validator->validate();
        $account = $this->_accountManager->getAccountByUsername($data['username']);
        if ($account === null) {
            throw new Exception('Failed to find an account with the user name: '.$data['username']);
        }

        return $this->doLogin($request, $account);
    }

    public function registerWithPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => [
                'required',
                'email',
                function (string $attribute, mixed $value, Closure $fail) {
                    if ($this->_accountManager->getAccountByUsername($value) !== null) {
                        $fail('An account already exists with that e-mail address.');
                    }
                }
            ],
            'password' => [ 'required', 'confirmed', Password::defaults() ]
        ]);

        $data = $validator->validate();
        $user = $this->_accountManager->createAccount(
            $data['username'],
            null,
            null,
            $data['password']
        );

        return $this->doLogin($request, $user);
    }

    private function redirectOnSystemError(string $providerName)
    {
        // Figure out if the user originated from the sign up or the sign in page.
        // Redirect them to the correct origin.
        // We're hard coding the options here to avoid HTTP_REFERER spoofing, deliberately
        // defaulting to `login` if the path doesn't exactly match what we'd expect of the
        // `register` route.
        $routeName = 'login';
        if (isset($_SERVER['HTTP_REFERER'])) {
            $referrer = $_SERVER['HTTP_REFERER'];
            if (parse_url($referrer, PHP_URL_PATH) === route('register', [], false)) {
                $routeName = 'register';
            }
        }

        return redirect()->to(route($routeName, ['error' => $providerName]));
    }

    private function doLogin(Request $request, Account $user, bool $first = false)
    {
        $user = $user->master_account ?: $user;

        if (! $user->memberOf(RoleConstants::Users)) {
            throw new \Exception('You are not authorized to sign in. You are missing the '. //
                RoleConstants::Users.' role.');
        }

        // If the account is linked, sign in as the master account
        auth()->login($user);

        event(new AccountAuthenticated($user, $first));

        if ($request->session()->has('auth.redirect')) {
            $path = $request->session()->pull('auth.redirect');
            return redirect($path);
        }
        
        return redirect()->route('author.my-profile', [ 'loggedIn' => true ]);
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

    private function log(string $method, string $provider, \Throwable $ex)
    {
        $this->_systemErrorRepository->saveException($ex, $provider.'-auth-'.$method);
    }
}
