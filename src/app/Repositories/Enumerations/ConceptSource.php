<?php

namespace App\Repositories\Enumerations;

/**
 * Who assigned a concept to a sense.
 */
enum ConceptSource: string
{
    // the headword has exactly one WordNet meaning
    case RULE = 'rule';
    // decided once, offline, for the senses that existed before concepts did
    case BACKFILL = 'backfill';
    // decided when a contribution introduced the sense
    case GEMINI = 'gemini';
    case EDITOR = 'editor';
}
