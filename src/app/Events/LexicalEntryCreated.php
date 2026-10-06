<?php

namespace App\Events;

use App\Models\LexicalEntry;
use Illuminate\Queue\SerializesModels;

class LexicalEntryCreated
{
    use SerializesModels;

    public function __construct(readonly LexicalEntry $lexicalEntry, readonly int $accountId)
    {}
}
