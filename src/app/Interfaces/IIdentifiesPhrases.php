<?php

namespace App\Interfaces;

use Illuminate\Support\Collection;

interface IIdentifiesPhrases 
{
    /**
     * Identifies key phrases from the specified text.
     */
    function detectKeyPhrases(string $text): Collection;
}
