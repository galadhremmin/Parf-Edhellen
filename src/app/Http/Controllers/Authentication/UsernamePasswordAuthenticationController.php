<?php

namespace App\Http\Controllers\Authentication;

use App\Events\AccountPasswordForgot;
use App\Exceptions\SuspiciousBotActivityException;
use App\Models\Account;
use App\Security\RoleConstants;
use Closure;
use Exception;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password as FacadesPassword;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use App\Helpers\RecaptchaHelper;

class UsernamePasswordAuthenticationController extends AuthenticationController
{
    public function forgotPassword(Request $request)
    {
        $status = $request->query('status', null);

        return view('authentication.forgot-password', [
            'status' => $status,
        ]);
    }

    public function loginWithPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => ['required', 'string'],
            'remember' => ['boolean'],
            'password' => [
                'required',
                'string',
                function (string $attribute, mixed $value, Closure $fail) use ($request) {
                    $username = $request->input('username');
                    if (empty($username)) {
                        $fail('You need to specify an e-mail address.');
                    } else if (! $this->_accountManager->checkPasswordWithUsername($username, $value)) {
                        $fail('We did not find an account with that e-mail and password combination. Check your e-mail and password and try again.');
                    }
                },
            ],
        ]);

        $data = $validator->validate();
        $account = $this->_accountManager->getAccountByUsername($data['username']);
        if ($account === null) {
            throw new Exception('Failed to find an account with the user name: '.$data['username']);
        }

        $remember = isset($data['remember']) ? boolval($data['remember']) : false;

        return $this->doLogin($request, $account, /* new: */ false, $remember);
    }

    public function registerWithPassword(Request $request)
    {
        // Protect the registration flow against binary actors.
        $assessmentResult = [];
        if (! empty($request->input('account_control')) || ! RecaptchaHelper::createAssessment($request->input('recaptcha_token'), 'REGISTER', $assessmentResult)) {
            $this->_systemErrorRepository->saveException(new SuspiciousBotActivityException($request, 'user registration', $assessmentResult), 'security');
            return redirect()->to('login');
        }

        $validator = Validator::make($request->all(), [
            'nickname' => [
                'required',
                'string',
                'max:64',
                'unique:accounts,nickname',
            ],
            'username' => [
                'required',
                'email',
                function (string $attribute, mixed $value, Closure $fail) {
                    if ($this->_accountManager->getAccountByUsername($value) !== null) {
                        $fail('An account already exists with that e-mail address.');
                    }
                },
            ],
            'password' => [
                'required',
                'confirmed',
                Password::defaults(),
            ],
            'remember' => ['boolean'],
        ]);

        $data = $validator->validate();
        $user = $this->_accountManager->createAccount(
            $data['username'],
            null,
            null,
            $data['password'],
            $data['nickname']
        );

        return $this->doLogin($request, $user, /* new: */ true, /* remember: */ false);
    }

    public function requestPasswordReset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => [
                'required',
                function (string $attribute, mixed $value, Closure $fail) {
                    $account = $this->_accountManager->getAccountByUsername($value);
                    if ($account === null || ! $account->is_passworded) {
                        $fail('We cannot find an account with that e-mail address.');
                    }
                    else if (! $account->memberOf(RoleConstants::Users)) {
                        $fail('This account needs to be activated by an administrator.');
                    }
                },
            ],
        ]);

        $data = $validator->validate();
        $filter = [
            'email' => $data['username'],
            'is_master_account' => 1,
        ];
        $user = Account::where($filter)->firstOrFail();
        $status = FacadesPassword::sendResetLink($filter);

        if ($status === FacadesPassword::RESET_LINK_SENT) {
            event(new AccountPasswordForgot($user));

            return redirect()->route('auth.forgot-password', ['status' => __($status)]);
        }

        throw ValidationException::withMessages([
            'username' => [__($status)],
        ]);
    }

    public function initiatePasswordResetFromEmail(Request $request, string $token)
    {
        $data = $request->validate([
            'email' => 'email|required',
        ]);

        return view('authentication.reset-password', [
            'token' => $token,
            'email' => $data['email'],
        ]);
    }

    public function completePasswordReset(Request $request, string $token)
    {
        $data = $request->validate([
            'email' => 'email|required',
            'password' => [
                'required',
                'confirmed',
                Password::defaults(),
            ],
        ]);

        $status = FacadesPassword::reset(
            $data + [
                'token' => $token,
            ],
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        if ($status == FacadesPassword::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', __($status));
        }

        throw ValidationException::withMessages([
            'password' => [trans($status)],
        ]);
    }
}
