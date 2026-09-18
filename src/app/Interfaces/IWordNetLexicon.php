<?php

namespace App\Interfaces;

use App\Services\Enumerations\WordNetPos;

/**
 * The WordNet lookups lemmatisation needs. An interface so that the normaliser can be tested against a stub.
 */
interface IWordNetLexicon
{
    /**
     * @return string[] the base forms of an irregular inflection (elves → elf); empty for a regular one
     */
    public function exceptionBases(string $form, WordNetPos $pos): array;

    /**
     * @param  WordNetPos|null  $pos  null for any part of speech
     */
    public function isLemma(string $lemma, ?WordNetPos $pos = null): bool;

    /**
     * How often the lemma was tagged in WordNet's corpus, across all parts of speech.
     */
    public function tagCount(string $lemma): int;
}
