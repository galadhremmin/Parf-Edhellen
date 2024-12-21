<?php

namespace App\Events;

use App\Models\Account;
use Illuminate\Queue\SerializesModels;

class EmailVerificationSent
{
    use SerializesModels;

    public Account $user;

    public function __construct(Account $user)
    {
        $this->user = $user;
    }
}
