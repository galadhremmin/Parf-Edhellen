<?php

namespace App\Events;

use App\Models\LexicalEntry;
use Illuminate\Queue\SerializesModels;

class LexicalEntryDestroyed
{
    use SerializesModels;

    public LexicalEntry $lexicalEntry;

    public ?LexicalEntry $replacementLexicalEntry;

    public int $accountId;

    public function __construct(LexicalEntry $lexicalEntry, ?LexicalEntry $replacement = null, $accountId = 0)
    {
        $this->lexicalEntry = $lexicalEntry;
        $this->replacementLexicalEntry = $replacement;
        $this->accountId = $accountId;
    }
}
