<?php

namespace App\Events;

use App\Models\Gloss;
use Illuminate\Queue\SerializesModels;

class GlossDestroyed
{
    use SerializesModels;

    public $gloss;
    public $replacementGloss;

    public function __construct(Gloss $gloss, Gloss $replacement = null)
    {
        $this->gloss = $gloss;
        $this->replacementGloss = $replacement;
    }
}
