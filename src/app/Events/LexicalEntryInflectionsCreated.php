<?php

namespace App\Events;

use App\Models\LexicalEntry;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class LexicalEntryInflectionsCreated
{
    use SerializesModels;

    public function __construct(readonly LexicalEntry $lexicalEntry, readonly Collection $lexicalEntryInflections, readonly bool $incremental)
    {}
}
