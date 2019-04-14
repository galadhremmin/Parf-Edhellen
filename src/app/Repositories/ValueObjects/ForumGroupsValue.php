<?php

namespace App\Repositories\ValueObjects;

class ForumGroupsValue implements \JsonSerializable 
{
    use Traits\CanInitialize;

    public function __construct($properties)
    {
        $this->initializeAll($properties, [
            'groups',
            'number_of_threads',
        ]);
    }

    public function getGroups()
    {
        return $this->getValue('groups');
    }

    public function getNumberOfThreads() 
    {
        return $this->getValue('number_of_threads');
    }
}
