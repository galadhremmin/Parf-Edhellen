<?php

namespace App\Events;

use App\Models\Account;
use Illuminate\Queue\SerializesModels;

class AccountPasswordForgot
{
    use SerializesModels;

    public function __construct(readonly Account $account)
    {}
}
