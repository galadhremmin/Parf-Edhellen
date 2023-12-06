<?php

namespace App\Http\Controllers\Authentication;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{
    Auth,
    Session,
};

use App\Events\AccountAuthenticated;
use App\Http\Controllers\Abstracts\Controller;
use App\Repositories\SystemErrorRepository;
use App\Models\{ 
    Account, 
    AuthorizationProvider 
};
use App\Security\AccountManager;
use App\Security\RoleConstants;

class AuthenticationController extends Controller
{
    /**
     * @var SystemErrorRepository
     */
    protected $_systemErrorRepository;

    /**
     * @var AccountManager
     */
    protected $_accountManager;

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

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->to('/');
    }

    protected function redirectOnSystemError(string $providerName)
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

    protected function doLogin(Request $request, Account $user, bool $first = false, bool $remember = false)
    {
        $user = $user->master_account ?: $user;

        if (! $user->memberOf(RoleConstants::Users)) {
            throw new \Exception('You are not authorized to sign in. You are missing the '. //
                RoleConstants::Users.' role.');
        }

        // If the account is linked, sign in as the master account
        auth()->login($user, $remember);

        event(new AccountAuthenticated($user, $first));

        if ($request->session()->has('auth.redirect')) {
            $path = $request->session()->pull('auth.redirect');
            return redirect($path);
        }
        
        return redirect()->route('author.my-profile', [ 'loggedIn' => true ]);
    }

    protected function log(string $method, string $provider, \Throwable $ex)
    {
        $this->_systemErrorRepository->saveException($ex, $provider.'-auth-'.$method);
    }
}
