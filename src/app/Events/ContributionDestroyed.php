<?php

namespace App\Events;

use App\Models\Contribution;
use Illuminate\Queue\SerializesModels;

class ContributionDestroyed
{
    use SerializesModels;

    public function __construct(readonly Contribution $contribution, readonly int $accountId)
    {}
}
