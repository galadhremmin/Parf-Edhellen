<?php

namespace App\Repositories;

use App\Models\LexicalEntry;
use App\Models\Sense;
use App\Models\SenseTerm;
use App\Services\Flashcards\VerbSpeechCatalogue;
use App\Services\Senses\NormalizedTerm;
use App\Services\Senses\SenseNormalizer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SenseTermRepository
{
    public function __construct(
        protected readonly SenseNormalizer $_normalizer,
        protected readonly VerbSpeechCatalogue $_verbs,
    ) {}

    /**
     * Senses in use, with what normalisation needs loaded. A handful of legacy senses lost their word and are left out.
     *
     * @return Builder<Sense>
     */
    public function normalizable(): Builder
    {
        return Sense::whereHas('lexical_entries', fn ($query) => $query->active())
            ->whereHas('word')
            ->with([
                'word:id,word',
                'lexical_entries' => fn ($query) => $query->active()->select('id', 'sense_id', 'speech_id'),
            ])
            ->select('id');
    }

    /**
     * Replaces the terms of the given senses, which must come from `normalizable()`.
     *
     * @param  Collection<int, Sense>  $senses
     * @return int the number of terms written
     */
    public function rebuild(Collection $senses): int
    {
        $rows = $senses->flatMap(fn (Sense $sense) => $this->normalize($sense)
            ->map(fn (NormalizedTerm $term) => [
                'sense_id' => $sense->id,
                'position' => $term->position,
                'term' => $term->term,
                'term_key' => $term->key,
                'is_verb' => $term->isVerb,
            ]));

        DB::transaction(function () use ($senses, $rows) {
            SenseTerm::whereIn('sense_id', $senses->modelKeys())->delete();
            SenseTerm::insert($rows->all());
        });

        return $rows->count();
    }

    /**
     * The terms of one sense, keyed as a verb when only verbs use it.
     *
     * @param  Sense  $sense  from `normalizable()`
     * @return Collection<int, NormalizedTerm>
     */
    public function normalize(Sense $sense): Collection
    {
        return $this->_normalizer->normalize($sense->word->word, $this->isVerb($sense));
    }

    /**
     * Brings one sense's terms up to date, removing them when no active entry uses the sense any more.
     */
    public function rebuildSense(int $senseId): void
    {
        $senses = $this->normalizable()->whereKey($senseId)->get();
        if ($senses->isEmpty()) {
            SenseTerm::where('sense_id', $senseId)->delete();

            return;
        }

        $this->rebuild($senses);
    }

    /**
     * The senses a search should widen to.
     *
     * @return Collection<int, int> senses with a term matching the query's first term. A query without "to" also
     *                              matches verbs, which are often glossed without it.
     */
    public function senseIdsMatching(string $query): Collection
    {
        $keys = $this->queryKeys($query);
        if ($keys->isEmpty()) {
            return collect();
        }

        return SenseTerm::whereIn('term_key', $keys)->distinct()->pluck('sense_id');
    }

    /**
     * The most common spelling of the query's headword, e.g. "tree" for "trees"; null when no sense has it.
     */
    public function headwordFor(string $query): ?string
    {
        $key = $this->queryKeys($query)->first();
        if ($key === null) {
            return null;
        }

        return SenseTerm::where('position', 0)
            ->where('term_key', $key)
            ->groupBy('term')
            ->orderByRaw('COUNT(*) DESC')
            ->orderByRaw('CHAR_LENGTH(term)')
            ->value('term');
    }

    /**
     * The keys a search matches: the key of its first term, plus the verb key when the query has no "to".
     *
     * @return Collection<int, string>
     */
    private function queryKeys(string $query): Collection
    {
        $term = $this->_normalizer->normalize($query)->first();
        if ($term === null) {
            return collect();
        }

        return $term->isVerb ? collect([$term->key]) : collect([$term->key, 'to:'.$term->key]);
    }

    /**
     * A sense used only by verbs is a verb even when written without "to".
     */
    private function isVerb(Sense $sense): bool
    {
        return $sense->lexical_entries->every(fn (LexicalEntry $entry) => $this->_verbs->isVerb($entry->speech_id));
    }
}
