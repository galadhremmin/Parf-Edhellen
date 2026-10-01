<?php

namespace App\Security\Identity;

use Laravel\Socialite\Contracts\User as SocialiteUser;

/**
 * Google states whether it has verified the address (`email_verified` in its userinfo response).
 */
class GoogleIdentityProvider extends SocialiteIdentityProvider
{
    protected function isEmailVerified(SocialiteUser $user): bool
    {
        $raw = method_exists($user, 'getRaw') ? $user->getRaw() : [];

        // Strictly true: a missing or malformed claim is an unverified one.
        return ($raw['email_verified'] ?? null) === true;
    }
}
