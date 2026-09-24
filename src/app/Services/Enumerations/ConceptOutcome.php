<?php

namespace App\Services\Enumerations;

/**
 * What resolving a sense's concept came to. The commands count these; nothing else branches on them.
 */
enum ConceptOutcome: string
{
    case ASSIGNED = 'assigned';
    case ASSIGNED_BY_RULE = 'assigned by rule';
    case INHERITED = 'inherited from the headword';
    case NOT_A_CONCEPT = 'not a concept';
    case NEEDS_REVIEW = 'waiting for an editor';
    case ALREADY_ASSIGNED = 'already assigned';
    case LEFT_AS_IS = 'left as it was';
    case LOCKED = 'locked by an editor';
}
