<?php

namespace App\Interfaces;

use App\Models\LexicalEntry;

interface IComposesWordOfTheDayTweet
{
    /**
     * Compose the body text of a tweet for the given lexical entry.
     *
     * The returned string does not include the language prefix, URL, or hashtags —
     * those are appended by the caller.
     *
     * Implementations must never throw; they must fall back to plain gloss text on failure.
     */
    public function composeTweet(LexicalEntry $entry): string;
}
