<?php

namespace App\Events;

use App\Models\Translation;
use Illuminate\Queue\SerializesModels;

class TranslationCreated
{
    use SerializesModels;

    public $translation;
    public $accountId;

    public function __construct(Translation $translation, int $accountId)
    {
        $this->translation = $translation;
        $this->accountId = $accountId;
    }
}
