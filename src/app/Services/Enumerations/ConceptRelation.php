<?php

namespace App\Services\Enumerations;

/**
 * How a word a judge offered stands to the sense, when no WordNet candidate fitted.
 */
enum ConceptRelation: string
{
    // the word means the same: "bright" for "light, bright, sunny"
    case SYNONYM = 'synonym';
    // the sense is a kind of the word: "mallorn" is a kind of "tree"
    case KIND_OF = 'kind_of';
}
