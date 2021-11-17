<?php

namespace App\Repositories\ValueObjects;

class ForumThreadFilterValue implements \JsonSerializable 
{
    use Traits\CanInitialize;

    public function __construct(array $properties)
    {
        $this->initializeAll($properties, [
            'forum_group', 'account', 'page_number', 'filter_names'
        ], /* required: */ false);
    }

    public function getForumGroup() 
    {
        return $this->getValue('forum_group') ?: null;
    }

    public function getAccount() 
    {
        return $this->getValue('account') ?: null;
    }

    public function getPageNumber() 
    {
        return $this->getValue('page_number') ?: 0;
    }

    public function getFilterNames() 
    {
        return $this->getValue('filter_names') ?: [];
    }
}
