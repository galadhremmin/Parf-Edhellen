<?php

namespace App\Services\Enumerations;

/**
 * What a sense glosses. Only lexical senses mean something a concept can capture.
 */
enum SenseKind: string
{
    case LEXICAL = 'lexical';
    // Aldaron, Eglarest
    case NAME = 'name';
    // "pronominal suffix", "superlative ending"
    case GRAMMAR = 'grammar';
    // we, the, out of
    case FUNCTION_WORD = 'function';
    // the sense repeats the entry's own word and says nothing in English: "#manda", "adhanc"
    case UNTRANSLATED = 'untranslated';
}
