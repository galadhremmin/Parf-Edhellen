<?php

namespace App\Interfaces;

interface IRephrasesCrosswordClues
{
    /**
     * Given an array of crossword clue objects (each containing at minimum
     * 'clue' and 'answer' keys), return the same array with the 'clue' values
     * rephrased into indirect crossword-style hints.
     *
     * Implementations must never modify the 'answer' field.
     * On failure, implementations must return the original array unchanged.
     *
     * @param  array<int, array<string, mixed>>  $clues
     * @return array<int, array<string, mixed>>
     */
    public function rephraseClues(array $clues): array;
}
