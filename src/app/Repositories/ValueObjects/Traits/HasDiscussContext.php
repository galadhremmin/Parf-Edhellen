<?php

namespace App\Repositories\ValueObjects\Traits;

trait HasDiscussContext
{
    public function setupDiscussContext(array $properties)
    {
        $this->initializeAll($properties, [
            'context',
        ]);
    }

    public function getContext()
    {
        return $this->getValue('context');
    }
}
