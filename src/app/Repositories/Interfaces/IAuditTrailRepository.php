<?php

namespace App\Repositories\Interfaces;

interface IAuditTrailRepository
{
    function get(int $noOfRows, int $skipNoOfRows = 0, array $action_ids = []);
    function store(int $action, $entity, int $userId = 0, bool $is_elevated = null, ?array $data = null);
}