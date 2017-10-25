<?php

namespace App\Events;

use App\Models\Speech;
use Illuminate\Queue\SerializesModels;

class SpeechDestroyed
{
    use SerializesModels;

    public $speech;
    public function __construct(Speech $speech)
    {
        $this->speech = $speech;
    }
}
