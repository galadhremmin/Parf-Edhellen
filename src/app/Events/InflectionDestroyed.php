<?php

namespace App\Events;

use App\Models\Inflection;
use Illuminate\Queue\SerializesModels;

class InflectionDestroyed
{
    use SerializesModels;

    public function __construct(readonly Inflection $inflection)
    {}
}
