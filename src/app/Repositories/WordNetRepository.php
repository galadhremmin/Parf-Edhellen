<?php

namespace App\Repositories;

use App\Interfaces\IWordNetLexicon;
use App\Models\WordNetException;
use App\Models\WordNetHypernym;
use App\Models\WordNetSense;
use App\Models\WordNetSynset;
use App\Services\Enumerations\WordNetPos;
use Illuminate\Support\Collection;

/**
 * Remembers every lookup: WordNet never changes at runtime, and a bulk normalisation asks about the same words
 * thousands of times.
 */
class WordNetRepository implements IWordNetLexicon
{
    // deeper than any WordNet hierarchy (entity → … → oak is 13), in case a pointer ever loops
    private const MAX_DEPTH = 32;

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
     * The synsets any of the spellings belongs to, most commonly tagged first: the order WordNet lists meanings in.
     *
     * @param  string[]  $lemmas  spellings of one word: "oak tree", "oak-tree", "oaktree"
     * @param  WordNetPos[]  $pos
     * @return Collection<int, string> synset IDs
     */
    public function synsetIdsFor(array $lemmas, array $pos): Collection
    {
        return WordNetSense::whereIn('lemma', $lemmas)
            ->whereIn('pos', collect($pos)->map(fn (WordNetPos $p) => $p->value))
            ->groupBy('synset_id')
            ->orderByRaw('SUM(tag_count) DESC')
            ->orderByRaw('MIN(sense_number)')
            ->pluck('synset_id');
    }

    /**
     * The hypernym a concept hangs under. Where WordNet lists several, a class beats an instance, then the lowest ID
     * wins, so the choice is stable across imports.
     */
    public function primaryHypernymId(string $synsetId): ?string
    {
        return WordNetHypernym::where('synset_id', $synsetId)
            ->orderBy('is_instance')
            ->orderBy('hypernym_id')
            ->value('hypernym_id');
    }

    /**
     * Whether the synset is one particular thing (Wisconsin, the Chinese Wall) rather than a kind of thing.
     */
    public function isInstance(string $synsetId): bool
    {
        return WordNetHypernym::where('synset_id', $synsetId)->where('is_instance', true)->exists()
            && ! WordNetHypernym::where('synset_id', $synsetId)->where('is_instance', false)->exists();
    }

    /**
     * The synset and its primary hypernyms up to the root, nearest first: oak, tree, woody plant, …
     *
     * @return Collection<int, WordNetSynset>
     */
    public function lineage(string $synsetId): Collection
    {
        $lineage = collect();
        for ($id = $synsetId; $id !== null && $lineage->count() < self::MAX_DEPTH; $id = $this->primaryHypernymId($id)) {
            $lineage->push(WordNetSynset::findOrFail($id));
        }

        return $lineage;
    }

    /**
     * The lemma's senses, fetched once per lemma.
     *
     * @return Collection<int, WordNetSense>
     */
    private function senses(string $lemma): Collection
    {
        return $this->_senses[$lemma] ??= WordNetSense::where('lemma', $lemma)->get(['pos', 'tag_count']);
    }
}
