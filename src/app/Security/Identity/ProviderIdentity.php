<?php

namespace App\Security\Identity;

/**
 * A person as an identity provider describes them.
 */
final class ProviderIdentity
{
    public function __construct(
        /** The provider's stable id for the person; never their e-mail, which can change. */
        public readonly string $subject,
        public readonly ?string $email,
        /**
         * True only when the provider vouches that the person controls `$email`. Anything less, and
         * we prove the address ourselves.
         */
        public readonly bool $emailVerified,
        public readonly ?string $name,
    ) {}

    /**
     * Whether the provider vouches for this particular address. The address we hold may predate a
     * change at the provider, so a claim about a different one says nothing about ours.
     */
    public function vouchesFor(?string $email): bool
    {
        return $this->emailVerified
            && $email !== null
            && $this->email !== null
            && strcasecmp($email, $this->email) === 0;
    }
}
