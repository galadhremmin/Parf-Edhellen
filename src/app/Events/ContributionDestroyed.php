<?php

namespace App\Events;

use App\Models\Contribution;
use Illuminate\Queue\SerializesModels;

class ContributionDestroyed
{
    use SerializesModels;

    public Contribution $contribution;

    public int $accountId;

    public function __construct(Contribution $contribution, int $accountId)
    {
        $this->contribution = $contribution;
        $this->accountId = $accountId;
    }
}
