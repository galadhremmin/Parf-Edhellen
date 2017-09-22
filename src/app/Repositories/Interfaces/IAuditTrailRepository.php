<?php

namespace App\Repositories\Interfaces;

interface IAuditTrailRepository
{
    function get(int $noOfRows, int $skipNoOfRows = 0, $previousItem = null);
    function store(int $action, $entity, int $userId = 0, bool $is_elevated = null);
}