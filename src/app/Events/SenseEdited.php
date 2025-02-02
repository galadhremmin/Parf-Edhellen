<?php

namespace App\Events;

use App\Models\Sense;
use Illuminate\Queue\SerializesModels;

class SenseEdited
{
    use SerializesModels;

    public Sense $sense;

    public function __construct(Sense $sense)
    {
        $this->sense = $sense;
    }
}
