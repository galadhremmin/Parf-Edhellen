<?php

namespace App\Repositories\Interfaces;

use App\Models\Account;

interface IAuditTrailRepository
{
    /**
     * Cache key for the audit trail shown on the front page. Shared between the writer (HomeController)
     * and surfaces that must invalidate it when public activity changes (e.g. hiding a spammer).
     */
    public const HOME_CACHE_KEY = 'ed.home.audit';

    public function get(int $noOfRows, int $skipNoOfRows = 0, array $action_ids = [], bool $publicOnly = false);

    public function store(int $action, $entity, int $userId = 0, ?bool $is_elevated = null, ?array $data = null);

    public function hideForAccount(Account $account): void;
}
