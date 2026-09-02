<?php

namespace App\Services\Flashcards;

/**
 * Which way round a flashcard is asked.
 *
 * Lives beside the rest of the flashcard services rather than in a general enumerations namespace:
 * the direction only means anything to this game.
 */
enum FlashcardDirection: string
{
    /** Elvish word on the front, translations offered as options. */
    case Forward = 'forward';

    /** Translation on the front, Elvish words offered as options. */
    case Reverse = 'reverse';

    public static function parse(?string $value): self
    {
        return self::tryFrom($value ?? '') ?? self::Forward;
    }
}
