<?php

namespace App\Events;

use App\Models\Sense;
use Illuminate\Queue\SerializesModels;

class SenseEdited
{
    use SerializesModels;

    public function __construct(readonly Sense $sense)
    {}
}
