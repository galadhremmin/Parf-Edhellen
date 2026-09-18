<?php

namespace App\Services\Enumerations;

/**
 * WordNet's parts of speech, by the letter WordNet uses for them.
 */
enum WordNetPos: string
{
    case NOUN = 'n';
    case VERB = 'v';
    case ADJECTIVE = 'a';
    case ADJECTIVE_SATELLITE = 's';
    case ADVERB = 'r';

    /**
     * The data file a synset of this part of speech lives in. Satellites share the adjective file.
     */
    public function file(): string
    {
        return match ($this) {
            self::NOUN => 'noun',
            self::VERB => 'verb',
            self::ADJECTIVE, self::ADJECTIVE_SATELLITE => 'adj',
            self::ADVERB => 'adv',
        };
    }
}
