<?php

namespace App\Events;

use App\Models\Gloss;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class GlossInflectionsCreated
{
    use SerializesModels;

    public $gloss;
    public $gloss_inflections;
    public $incremental;
    
    public function __construct(Gloss $gloss, Collection $inflections, bool $incremental)
    {
        $this->gloss = $gloss;
        $this->gloss_inflections = $inflections;
        $this->incremental = $incremental;
    }
}
