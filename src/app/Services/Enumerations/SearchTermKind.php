<?php

namespace App\Services\Enumerations;

/**
 * Where a search term comes from. The values are the labels the audit prints, which is what tells a
 * phrase-derived gap (searchable through the phrase) apart from one that hides a word entirely.
 */
enum SearchTermKind: string
{
    case KEYWORD = 'keyword';
    case GLOSS = 'gloss';
    case INFLECTION = 'inflection';
    case PHRASE_KEYWORD = 'keyword (from a phrase)';
    case PHRASE_INFLECTION = 'inflection (from a phrase)';
}
