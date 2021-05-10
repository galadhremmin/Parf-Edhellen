<?php

namespace App\Repositories\ValueObjects;

class SpecificEntitiesSearchValue extends SearchIndexSearchValue
{
    public function __construct(array $ids)
    {
        parent::__construct([]);

        $args = [ 'ids' => $ids ];
        $this->initialize($args, 'ids', true /* required */);
    }

    public function getIds() 
    {
        return $this->getValue('ids');
    }
}
