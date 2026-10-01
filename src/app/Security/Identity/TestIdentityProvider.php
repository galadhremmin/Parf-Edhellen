<?php

namespace App\Security\Identity;

use App\Interfaces\IIdentityProvider;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * A sign-in form that lets you be anyone, for trying out account linking and e-mail verification
 * locally. Switched on by ED_TEST_IDENTITY_PROVIDER=true, and refuses to work outside APP_ENV=local
 * whatever the configuration says.
 */
class TestIdentityProvider implements IIdentityProvider
{
    public function __construct(
        protected readonly string $_nameIdentifier,
    ) {}

    public function isAvailable(): bool
    {
        return app()->isLocal();
    }

    /**
     * Instead of a trip to a provider, a form; it submits straight to the callback.
     */
    public function redirect(Request $request): Response
    {
        return response()->view('authentication.test-identity-provider', [
            'callbackUrl' => url('/federated-auth/callback/'.$this->_nameIdentifier),
        ]);
    }

    public function resolveIdentity(Request $request): ProviderIdentity
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:128'],
            'name' => ['nullable', 'string', 'max:64'],
            'identity' => ['nullable', 'string', 'max:48'],
            'verified' => ['nullable', 'boolean'],
        ]);

        // The same address signs back into the same account unless another identity is asked for.
        $identity = trim($data['identity'] ?? '') ?: $data['email'];

        return new ProviderIdentity(
            'test|'.$identity,
            $data['email'],
            (bool) ($data['verified'] ?? false),
            $data['name'] ?? null,
        );
    }
}
