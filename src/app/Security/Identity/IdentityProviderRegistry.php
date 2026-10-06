<?php

namespace App\Security\Identity;

use App\Interfaces\IIdentityProvider;
use Illuminate\Contracts\Container\Container;

/**
 * The identity providers registered in `ed.identity_providers`, by `name_identifier`. Only those
 * available in this environment are ever handed out.
 */
class IdentityProviderRegistry
{
    /** @var array<string, IIdentityProvider|null> */
    private array $_resolved = [];

    public function __construct(
        protected readonly Container $_container,
    ) {}

    public function find(string $nameIdentifier): ?IIdentityProvider
    {
        if (! array_key_exists($nameIdentifier, $this->_resolved)) {
            $this->_resolved[$nameIdentifier] = $this->make($nameIdentifier);
        }

        return $this->_resolved[$nameIdentifier];
    }

    /**
     * @return string[] the `name_identifier` of every provider that can be used here
     */
    public function availableNameIdentifiers(): array
    {
        return array_values(array_filter(
            array_keys(config('ed.identity_providers', [])),
            fn (string $nameIdentifier) => $this->find($nameIdentifier) !== null,
        ));
    }

    private function make(string $nameIdentifier): ?IIdentityProvider
    {
        $className = config('ed.identity_providers')[$nameIdentifier] ?? null;
        if ($className === null) {
            return null;
        }

        $provider = $this->_container->make($className, ['_nameIdentifier' => $nameIdentifier]);
        if (! $provider instanceof IIdentityProvider) {
            throw new \UnexpectedValueException(sprintf('%s, registered for "%s", is not an identity provider.', $className, $nameIdentifier));
        }

        return $provider->isAvailable() ? $provider : null;
    }
}
