<?php

namespace App\Interfaces;

interface IPostsTweet
{
    /**
     * Post a tweet and return true on success.
     *
     * Implementations must never throw; they must log failures and return false.
     */
    public function postTweet(string $text): bool;
}
