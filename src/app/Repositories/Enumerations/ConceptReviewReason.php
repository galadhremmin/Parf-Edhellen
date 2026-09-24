<?php

namespace App\Repositories\Enumerations;

/**
 * Why a sense is waiting for an editor.
 */
enum ConceptReviewReason: string
{
    // WordNet has no meaning for the headword, and the model offered no word either
    case NO_MEANING = 'no_meaning';
    // the model answered, but not confidently enough to assign
    case UNSURE = 'unsure';
    // the word the model offered isn't in WordNet either
    case UNKNOWN_WORD = 'unknown_word';
    // nobody could be asked: the judge is switched off, out of quota, or failed
    case NOT_JUDGED = 'not_judged';
    // the answer named a meaning that was never on offer, so it cannot be trusted
    case INVALID_ANSWER = 'invalid_answer';
}
