<?php

namespace App\Resolvers;

class EdResolver
{
    public function __construct(private int $_version)
    { }

    public function __invoke(string $identifier): string
    {
        return public_path('v'.$this->_version.'-server/'.$identifier.'.js');
    }
}
