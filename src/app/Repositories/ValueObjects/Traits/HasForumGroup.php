<?php

namespace App\Repositories\ValueObjects\Traits;

use App\Models\ForumGroup;

trait HasForumGroup
{
    public function setupForumGroup(array $properties)
    {
        $this->initializeAll($properties, [
            'group'
        ]);
    }

    /**
     * @return ForumGroup
     */
    public function getGroup() 
    {
        return $this->getValue('group');
    }
}
