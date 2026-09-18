<?php

namespace App\Services\ValueObjects;

/**
 * How many entries an audit looked at. Entries with no index at all are counted apart, because a missing
 * index and an incomplete one are repaired differently.
 */
class SearchIndexAuditTotals
{
    public function __construct(
        public readonly int $checked,
        public readonly int $unindexed,
    ) {}
}
