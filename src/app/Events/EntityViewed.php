<?php

namespace App\Events;

use App\Repositories\ValueObjects\SearchIndexSearchValue;

class EntityViewed
{
    public function __construct(
        public readonly int $groupId,
        public readonly SearchIndexSearchValue $searchValue,
        public readonly array $entities,
    ) {
    }
}
