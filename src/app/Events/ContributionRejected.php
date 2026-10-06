<?php

namespace App\Events;

use App\Models\Contribution;
use Illuminate\Queue\SerializesModels;

class ContributionRejected
{
    use SerializesModels;

    public function __construct(readonly Contribution $contribution)
    {}
}
