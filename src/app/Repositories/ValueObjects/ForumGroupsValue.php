<?php

namespace App\Repositories\ValueObjects;

use Illuminate\Database\Eloquent\Collection;

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

    /**
     * Get a collection of groups
     *
     * @return Collection
     */
    public function getGroups()
    {
        return $this->getValue('groups');
    }

    /**
     * Gets number of threads associated with the groups keyed by group ID.
     *
     * @return Collection
     */
    public function getNumberOfThreads()
    {
        return $this->getValue('number_of_threads');
    }
}
