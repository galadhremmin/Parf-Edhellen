<?php

namespace App\Events;

use App\Models\Contribution;
use Illuminate\Queue\SerializesModels;

class ContributionApproved
{
    use SerializesModels;

    public function __construct(readonly Contribution $contribution)
    {}
}
