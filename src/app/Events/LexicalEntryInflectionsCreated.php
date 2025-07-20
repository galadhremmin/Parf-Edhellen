<?php

namespace App\Events;

use App\Models\LexicalEntry;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class LexicalEntryInflectionsCreated
{
    use SerializesModels;

    public LexicalEntry $lexicalEntry;

    public Collection $lexicalEntryInflections;

    public int $incremental;

    public function __construct(LexicalEntry $lexicalEntry, Collection $inflections, bool $incremental)
    {
        $this->lexicalEntry = $lexicalEntry;
        $this->lexicalEntryInflections = $inflections;
        $this->incremental = $incremental;
    }
}
