<?php

namespace App\Events;

use App\Models\Contribution;
use Illuminate\Queue\SerializesModels;

class ContributionApproved
{
    use SerializesModels;

    public $contribution;
    public function __construct(Contribution $contribution)
    {
        $this->contribution = $contribution;
    }
}
