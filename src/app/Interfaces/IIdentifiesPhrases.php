<?php

namespace App\Interfaces;

interface IIdentifiesPhrases
{
    /**
     * Identifies key phrases from the specified text.
     */
    public function detectKeyPhrases(string $text): array;
}
