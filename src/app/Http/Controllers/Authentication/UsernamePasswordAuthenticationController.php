<?php

namespace App\Http\Controllers\Authentication;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{
    Password as FacadesPassword,
    Validator
};
use Illuminate\Validation\Rules\Password;

use Closure;
use Exception;
use Illuminate\Validation\ValidationException;

class UsernamePasswordAuthenticationController extends AuthenticationController
{
    public function forgotPassword(Request $request)
    {
        $status = $request->query('status', null);
        return view('authentication.forgot-password', [
            'status' => $status
        ]);
    }

    public function loginWithPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => [ 'required', 'string' ],
            'remember' => [ 'boolean' ],
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

        $remember = isset($data['remember']) ? boolval($data['remember']) : false;
        return $this->doLogin($request, $account, /* new: */ false, $remember);
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
            'password' => [
                'required',
                'confirmed',
                Password::defaults()
            ],
            'remember' => [ 'boolean' ]
        ]);

        $data = $validator->validate();
        $user = $this->_accountManager->createAccount(
            $data['username'],
            null,
            null,
            $data['password']
        );

        return $this->doLogin($request, $user, /* new: */ true, /* remember: */ false);
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => [
                'required',
                function (string $attribute, mixed $value, Closure $fail) {
                    if ($this->_accountManager->getAccountByUsername($value) === null) {
                        $fail('We cannot find an account with that e-mail address.');
                    }
                }
            ]
        ]);

        $data = $validator->validate();
        $status = FacadesPassword::sendResetLink([
            'email' => $data['username'],
            'is_master_account' => 1
        ]);
        
        if ($status === FacadesPassword::RESET_LINK_SENT) {
            return redirect()->route('auth.forgot-password', ['status' => __($status)]);
        }

        throw ValidationException::withMessages([
            'username' => [__($status)],
        ]);
    }

    public function resetPasswordConfirmedFromEmail(Request $request)
    {
        dd($request->all()); // TODO
    }
}
