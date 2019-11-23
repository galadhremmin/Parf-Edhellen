<?php

namespace App\Repositories\ValueObjects\Traits;

use Exception;

trait CanInitialize
{
    private $_values;

    public function jsonSerialize()
    {
        return $this->getAllValues();
    }

    public function getAllValues()
    {
        return $this->_values;
    }

    protected function initialize(array &$properties, string $propertyName)
    {
        if (empty($propertyName) || ! is_string($propertyName)) {
            throw new Exception('Property name must be a valid string');
        }

        if (! isset($properties[$propertyName])) {
            throw new Exception(sprintf('The %s does not contain the required property %s.', serialize($properties), $propertyName));
        }

        if (! is_array($this->_values)) {
            $this->_values = [];
        }

        $this->_values[$propertyName] = $properties[$propertyName];
    }

    protected function initializeAll(array &$properties, array $propertyNames)
    {
        foreach ($propertyNames as $propertyName) {
            $this->initialize($properties, $propertyName);
        }
    }

    protected function getValue($propertyName)
    {
        if (! isset($this->_values[$propertyName])) {
            throw new Exception(sprintf('The property %s does not exist.', $propertyName));
        }

        return $this->_values[$propertyName];
    }
}
