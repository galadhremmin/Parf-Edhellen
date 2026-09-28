<?php

namespace App\Events;

use App\Models\LexicalEntry;
use Illuminate\Queue\SerializesModels;

class LexicalEntryDestroyed
{
    use SerializesModels;

    public function __construct(readonly LexicalEntry $lexicalEntry, readonly ?LexicalEntry $replacementLexicalEntry = null, readonly int $accountId = 0)
    {}
}
