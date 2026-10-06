<?php

namespace App\Events;

use App\Models\Speech;
use Illuminate\Queue\SerializesModels;

class SpeechDestroyed
{
    use SerializesModels;

    public function __construct(readonly Speech $speech)
    {}
}
