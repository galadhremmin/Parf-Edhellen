<?php

namespace Tests\Unit\Services\Senses;

use App\Interfaces\IWordNetLexicon;
use App\Services\Enumerations\WordNetPos;

/**
 * A few lemmas with their WordNet 3.1 tag counts, enough to exercise the lemmatiser without a database.
 */
class StubWordNetLexicon implements IWordNetLexicon
{
    private const LEMMAS = [
        'church' => ['n' => 62], 'day' => ['n' => 349], 'days' => ['n' => 1], 'door' => ['n' => 60],
        'elf' => ['n' => 0], 'eye' => ['n' => 288], 'eyes' => ['n' => 4], 'forest' => ['n' => 18],
        'gate' => ['n' => 19], 'good' => ['n' => 295], 'man' => ['n' => 1220], 'oak' => ['n' => 4],
        'pas' => ['n' => 0], 'pass' => ['n' => 11, 'v' => 172], 'people' => ['n' => 290], 'pine' => ['n' => 4],
        'sky' => ['n' => 25], 'specie' => ['n' => 0], 'species' => ['n' => 32], 'star' => ['n' => 41],
        'tooth' => ['n' => 23], 'tree' => ['n' => 107], 'wa' => ['n' => 0], 'wood' => ['n' => 41],
    ];

    private const EXCEPTIONS = ['elves' => ['elf'], 'men' => ['man'], 'teeth' => ['tooth']];

    public function exceptionBases(string $form, WordNetPos $pos): array
    {
        return $pos === WordNetPos::NOUN ? self::EXCEPTIONS[$form] ?? [] : [];
    }

    public function isLemma(string $lemma, ?WordNetPos $pos = null): bool
    {
        $counts = self::LEMMAS[$lemma] ?? [];

        return $pos === null ? $counts !== [] : isset($counts[$pos->value]);
    }

    public function tagCount(string $lemma): int
    {
        return array_sum(self::LEMMAS[$lemma] ?? []);
    }
}
