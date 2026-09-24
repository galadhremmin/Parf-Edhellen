<?php

namespace App\Services\Senses;

use App\Models\Sense;

/**
 * The concept a name belongs to. Gildir is a given name, Eglarest a place name: the entry's part of speech already
 * says which, so no judgement is needed. WordNet files all three under "name", so the hierarchy comes with them. The
 * parts of speech are rows an administrator can add, so what each one means lives in `config/senses.php`.
 */
class NameConcepts
{
    public function __construct(protected readonly SenseClassifier $_classifier) {}

    /**
     * The synset for the kind of name the sense's entries record, defaulting to "name" itself.
     *
     * @param  Sense  $sense  with `lexical_entries` loaded
     */
    public function synsetIdFor(Sense $sense): string
    {
        $bySpeech = config('senses.name_concepts');

        return $this->_classifier->speechesOf($sense)
            ->map(fn (string $speech) => $bySpeech[$speech] ?? null)
            ->filter()
            // the commonest kind among the entries; a word used as both a man's and a place's name is whichever more entries say
            ->countBy()
            ->sortDesc()
            ->keys()
            ->first() ?? config('senses.default_name_concept');
    }
}
