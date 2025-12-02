<?php

namespace App\Repositories\Interfaces;

use App\Models\Account;

interface IAuditTrailRepository
{
    public function get(int $noOfRows, int $skipNoOfRows = 0, array $action_ids = []);
    public function store(int $action, $entity, int $userId = 0, ?bool $is_elevated = null, ?array $data = null);
    public function hideForAccount(Account $account): void;
}
