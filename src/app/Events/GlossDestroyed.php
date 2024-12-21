<?php

namespace App\Events;

use App\Models\Gloss;
use Illuminate\Queue\SerializesModels;

class GlossDestroyed
{
    use SerializesModels;

    public Gloss $gloss;

    public ?Gloss $replacementGloss;

    public int $accountId;

    public function __construct(Gloss $gloss, ?Gloss $replacement = null, $accountId = 0)
    {
        $this->gloss = $gloss;
        $this->replacementGloss = $replacement;
        $this->accountId = $accountId;
    }
}
