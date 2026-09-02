<?php

namespace App\Services\Flashcards;

use App\Models\Speech;
use DateInterval;
use Illuminate\Support\Facades\Cache;

/**
 * Knows which parts of speech behave as verbs.
 *
 * Reads the `is_verb` flag rather than looking for a speech literally named "verb": several rows
 * carry the flag (see the quettaparma_quenyallo_verb_fix migration), and a single-row lookup
 * silently ignores all but one of them.
 */
class VerbSpeechCatalogue
{
    /**
     * @return int[]
     */
    public function getIds(): array
    {
        // Deliberately a distinct cache key from the older `ed.speech.v`: that one held a single
        // integer, and a deployment reusing it would hand an int to code expecting an array.
        return Cache::remember('ed.speech.verb-ids', DateInterval::createFromDateString('1 day'), function () {
            return Speech::where('is_verb', 1)->pluck('id')->all();
        });
    }

    public function isVerb(?int $speechId): bool
    {
        return $speechId !== null && in_array($speechId, $this->getIds(), true);
    }
}
