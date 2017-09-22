<?php

namespace App\Repositories\Noop;

use App\Repositories\Interfaces\IAuditTrailRepository;
use Illuminate\Database\Eloquent\Relations\Relation;

class NoopAuditTrailRepository implements IAuditTrailRepository
{
    public function get(int $noOfRows, int $skipNoOfRows = 0, $previousItem = null)
    {
        // Noop
    }

    public function store(int $action, $entity, int $userId = 0, bool $is_elevated = null)
    {
        // Noop
    }
}
