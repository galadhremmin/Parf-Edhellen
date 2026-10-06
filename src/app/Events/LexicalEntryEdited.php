<?php

namespace App\Events;

use App\Models\LexicalEntry;
use Illuminate\Queue\SerializesModels;

class LexicalEntryEdited
{
    use SerializesModels;

    public function __construct(readonly LexicalEntry $lexicalEntry, readonly int $accountId)
    {}
}
