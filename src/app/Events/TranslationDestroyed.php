<?php

namespace App\Events;

use App\Models\Translation;
use Illuminate\Queue\SerializesModels;

class TranslationDestroyed
{
    use SerializesModels;

    public $translation;
    public $replacementTranslation;

    public function __construct(Translation $translation, Translation $replacement = null)
    {
        $this->translation = $translation;
        $this->replacementTranslation = $replacement;
    }
}
