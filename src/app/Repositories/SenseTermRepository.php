<?php

namespace App\Repositories;

use App\Models\LexicalEntry;
use App\Models\Sense;
use App\Models\SenseConcept;
use App\Models\SenseTerm;
use App\Repositories\ValueObjects\SenseSuggestion;
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
                // the entries' own words and glosses say whether the sense translates anything
                'lexical_entries' => fn ($query) => $query->active()
                    ->select('id', 'sense_id', 'speech_id', 'word_id')
                    ->with(['word:id,word', 'glosses:id,lexical_entry_id,translation']),
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
                'lemma' => $term->lemma,
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
     * The keys a search is looked up by, the way senses are keyed: "trees" and "Tree" both come out as "tree", and a
     * query without "to" is also looked up as a verb.
     *
     * @return Collection<int, string>
     */
    public function keysFor(string $query): Collection
    {
        return $this->queryKeys($query);
    }

    /**
     * Wordings the dictionary already glosses words with, most used first. Offered so that a new entry can join an
     * existing sense instead of starting a near-identical one.
     *
     * @param  int|null  $conceptId  limits them to the wordings that mean this, so a contributor who has said what
     *                               their entry means is shown how others have worded it
     * @return Collection<int, SenseSuggestion>
     */
    public function suggestionsFor(string $query, int $limit, ?int $conceptId = null): Collection
    {
        $keys = $this->queryKeys($query);
        if ($keys->isEmpty() && $conceptId === null) {
            return collect();
        }

        return SenseTerm::where('position', 0)
            ->when($keys->isNotEmpty(), fn ($term) => $term->where(function ($match) use ($keys) {
                $keys->each(fn (string $key) => $match->orWhere('term_key', 'like', $key.'%'));
            }))
            ->when($conceptId, fn ($term, $concept) => $term->whereIn('sense_terms.sense_id',
                SenseConcept::where('concept_id', $concept)->select('sense_id')))
            ->join('words', 'words.id', 'sense_terms.sense_id')
            ->join('lexical_entries', function ($join) {
                $join->on('lexical_entries.sense_id', 'sense_terms.sense_id')->where('lexical_entries.is_deleted', 0);
            })
            ->groupBy('sense_terms.sense_id', 'words.word')
            ->orderByRaw('COUNT(lexical_entries.id) DESC')
            ->orderBy('words.word')
            ->limit($limit)
            ->get(['sense_terms.sense_id', 'words.word', DB::raw('COUNT(lexical_entries.id) AS entries')])
            ->map(fn (SenseTerm $term) => new SenseSuggestion($term->sense_id, $term->word, (int) $term->entries));
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
