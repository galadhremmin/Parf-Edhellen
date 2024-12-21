<?php

namespace App\Repositories\Enumerations;

enum GlossChange: int
{
    case NO_CHANGE = 0;
    case NEW = 1 << 0;
    case METADATA = 1 << 1;
    case DETAILS = 1 << 2;
    case TRANSLATIONS = 1 << 3;
    case KEYWORDS = 1 << 4;
    case WORD_OR_SENSE = 1 << 5;
}
