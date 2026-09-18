<?php

namespace App\Repositories;

use App\Interfaces\IWordNetLexicon;
use App\Models\WordNetException;
use App\Models\WordNetSense;
use App\Services\Enumerations\WordNetPos;
use Illuminate\Support\Collection;

/**
 * Remembers every lookup: WordNet never changes at runtime, and a bulk normalisation asks about the same words
 * thousands of times.
 */
class WordNetRepository implements IWordNetLexicon
{
    /** @var array<string, Collection<int, WordNetSense>> */
    private array $_senses = [];

    /** @var array<string, string[]> */
    private array $_exceptions = [];

    public function exceptionBases(string $form, WordNetPos $pos): array
    {
        return $this->_exceptions[$pos->value.':'.$form] ??= WordNetException::where('pos', $pos->value)
            ->where('form', $form)
            ->orderBy('base')
            ->pluck('base')
            ->all();
    }

    public function isLemma(string $lemma, ?WordNetPos $pos = null): bool
    {
        $senses = $this->senses($lemma);

        return $pos === null ? $senses->isNotEmpty() : $senses->contains('pos', $pos->value);
    }

    public function tagCount(string $lemma): int
    {
        return $this->senses($lemma)->sum('tag_count');
    }

    /**
     * @return Collection<int, WordNetSense>
     */
    private function senses(string $lemma): Collection
    {
        return $this->_senses[$lemma] ??= WordNetSense::where('lemma', $lemma)->get(['pos', 'tag_count']);
    }
}
