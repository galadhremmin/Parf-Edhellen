<?php

namespace App\Security\Identity;

use App\Interfaces\IIdentityProvider;
use Illuminate\Http\Request;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\Response;

/**
 * An OAuth provider through Socialite, whose driver shares the provider's `name_identifier`.
 * Enough as it is for providers that say nothing trustworthy about the e-mail address.
 */
class SocialiteIdentityProvider implements IIdentityProvider
{
    public function __construct(
        protected readonly string $_nameIdentifier,
    ) {}

    public function isAvailable(): bool
    {
        return true;
    }

    public function redirect(Request $request): Response
    {
        return Socialite::driver($this->_nameIdentifier)->redirect();
    }

    public function resolveIdentity(Request $request): ProviderIdentity
    {
        $user = Socialite::driver($this->_nameIdentifier)->user();

        return new ProviderIdentity(
            (string) $user->getId(),
            $user->getEmail(),
            $this->isEmailVerified($user),
            $user->getName(),
        );
    }

    /**
     * Whether the provider vouches for the address. Most don't say, so by default nobody is trusted.
     */
    protected function isEmailVerified(SocialiteUser $user): bool
    {
        return false;
    }
}
