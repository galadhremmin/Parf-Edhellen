<?php

namespace App\Interfaces;

use App\Security\Identity\ProviderIdentity;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Somewhere a person can sign in: Google, Discord, or the local test provider. Implementations are
 * registered by `name_identifier` in `ed.identity_providers`.
 */
interface IIdentityProvider
{
    /**
     * Whether this provider may be used in this environment. One that isn't is neither offered on
     * the login page nor accepted on the callback.
     */
    public function isAvailable(): bool;

    /**
     * Sends the person off to sign in. The provider returns them to the callback route.
     */
    public function redirect(Request $request): Response;

    /**
     * Who came back to the callback, and whether the provider vouches for their e-mail address.
     */
    public function resolveIdentity(Request $request): ProviderIdentity;
}
