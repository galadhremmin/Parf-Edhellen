<?php

namespace App\Events;

use App\Models\Inflection;
use Illuminate\Queue\SerializesModels;

class InflectionDestroyed
{
    use SerializesModels;

    public Inflection $inflection;

    public function __construct(Inflection $inflection)
    {
        $this->inflection = $inflection;
    }
}
