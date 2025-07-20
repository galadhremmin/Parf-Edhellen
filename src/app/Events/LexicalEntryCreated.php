<?php

namespace App\Events;

use App\Models\LexicalEntry;
use Illuminate\Queue\SerializesModels;

class LexicalEntryCreated
{
    use SerializesModels;

    public LexicalEntry $lexicalEntry;

    public int $accountId;

    public function __construct(LexicalEntry $lexicalEntry, int $accountId)
    {
        $this->lexicalEntry = $lexicalEntry;
        $this->accountId = $accountId;
    }
}
