<?php

namespace App\Events;

use App\Models\Gloss;
use Illuminate\Queue\SerializesModels;

class GlossDestroyed
{
    use SerializesModels;

    public $gloss;
    public $replacementGloss;
    public $accountId;

    public function __construct(Gloss $gloss, ?Gloss $replacement = null, $accountId = 0)
    {
        $this->gloss = $gloss;
        $this->replacementGloss = $replacement;
        $this->accountId = $accountId;
    }
}
