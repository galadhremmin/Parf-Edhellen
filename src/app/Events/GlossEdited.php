<?php

namespace App\Events;

use App\Models\Gloss;
use Illuminate\Queue\SerializesModels;

class GlossEdited
{
    use SerializesModels;

    public $gloss;
    public $accountId;

    public function __construct(Gloss $gloss, int $accountId)
    {
        $this->gloss = $gloss;
        $this->accountId = $accountId;
    }
}
