<?php

namespace App\Repositories;

use App\Models\Concept;
use App\Models\ConceptLabel;
use App\Models\LexicalEntry;
use App\Models\SenseConcept;
use App\Models\SenseConceptReview;
use App\Models\SenseTerm;
use App\Models\WordNetSense;
use App\Models\WordNetSynset;
use App\Repositories\Enumerations\ConceptReviewReason;
use App\Repositories\Enumerations\ConceptSource;
use App\Repositories\ValueObjects\ConceptAssignment;
use App\Repositories\ValueObjects\ConceptSuggestion;
use App\Repositories\ValueObjects\RelatedConcept;
use App\Services\Senses\SenseNormalizer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ConceptRepository
{
    private const CHUNK_SIZE = 1000;

    // WordNet's deepest chain is under 20; concept_closure.depth is a tinyint
    private const MAX_DEPTH = 64;

    /** @var array<string, Concept> */
    private array $_bySynset = [];

    public function __construct(
        protected readonly WordNetRepository $_wordNet,
        protected readonly SenseNormalizer $_normalizer,
    ) {}

    /**
     * The concept for a synset. The first time a synset is asked for, its concept is created together with the
     * concepts of its ancestors and the words it goes by.
     */
    public function forSynset(string $synsetId): Concept
    {
        if (isset($this->_bySynset[$synsetId])) {
            return $this->_bySynset[$synsetId];
        }

        $concept = Concept::firstWhere('synset_id', $synsetId);
        if ($concept === null) {
            $parentId = $this->_wordNet->primaryHypernymId($synsetId);
            $synset = WordNetSynset::findOrFail($synsetId);
            $concept = Concept::create([
                'label' => $synset->label,
                'synset_id' => $synsetId,
                'parent_id' => $parentId === null ? null : $this->forSynset($parentId)->id,
            ]);
            $this->addLabels($concept, $synset);
        }

        return $this->_bySynset[$synsetId] = $concept;
    }

    /**
     * One concept by ID, or null when it is not there.
     */
    public function find(int $conceptId): ?Concept
    {
        return Concept::find($conceptId);
    }

    /**
     * One concept as the form that chooses meanings shows them.
     */
    public function describe(int $conceptId): ?ConceptSuggestion
    {
        $concept = Concept::with('synset:id,definition')->find($conceptId);

        return $concept === null ? null : new ConceptSuggestion(
            $concept->id,
            $concept->label,
            (string) $concept->synset?->definition,
            $this->synonymsOf($concept),
            $this->lineageOf($concept),
            $this->entriesUnder([$concept->id], /* inclusive = */ true),
        );
    }

    /**
     * A concept for a word WordNet has no meaning for, under the concept of the word a judge said it is a kind of:
     * "mallorn" under "tree". Senses that answer the same way share it.
     */
    public function forWord(string $label, Concept $parent, string $termKey): Concept
    {
        $concept = Concept::firstOrCreate(['label' => $label, 'parent_id' => $parent->id, 'synset_id' => null]);
        ConceptLabel::firstOrCreate(['term_key' => $termKey, 'concept_id' => $concept->id]);

        return $concept;
    }

    /**
     * The concepts a result set is about: the ones most of the matched senses share, leaving out any so broad that
     * its kinds would bury the answer. A search for "water" turns on water and bodies of water, not on name, which
     * holds thousands of entries.
     *
     * @param  int[]  $senseIds  the senses whose headword the search matched, not everything it returned: a result
     *                           set holds stray names and metaphors whose kinds are nothing to do with the query
     * @return Collection<int, int> concept IDs
     */
    public function subjectConceptIds(array $senseIds): Collection
    {
        $subjects = SenseConcept::whereIn('sense_id', $senseIds)
            ->selectRaw('concept_id, COUNT(*) AS senses')
            ->groupBy('concept_id')
            ->orderByDesc('senses')
            ->orderBy('concept_id')
            ->limit(config('senses.subject_concepts'))
            ->pluck('senses', 'concept_id');

        if ($subjects->isEmpty()) {
            return collect();
        }

        // a minor meaning among the matches takes its kinds with it: one sense glossed "tree" that means a plant
        // should not put grass among the kinds of tree
        $leading = $subjects->max();

        return $subjects->filter(fn (int $senses) => $senses * 2 >= $leading)
            ->keys()
            ->filter(fn (int $conceptId) => $this->entriesUnder([$conceptId]) <= config('senses.max_entries_under_subject'))
            ->values();
    }

    /**
     * The senses of everything below these concepts: the oaks and alders under tree.
     *
     * @param  Collection<int, int>  $conceptIds
     * @param  bool  $inclusive  whether to take the concepts' own senses as well
     * @return Collection<int, int> sense IDs
     */
    public function senseIdsUnder(Collection $conceptIds, bool $inclusive = false): Collection
    {
        if ($conceptIds->isEmpty()) {
            return collect();
        }

        $concepts = $this->descendantIds($conceptIds);

        return SenseConcept::whereIn('concept_id', $inclusive ? $concepts->merge($conceptIds) : $concepts)
            ->distinct()
            ->pluck('sense_id');
    }

    /**
     * Meanings to offer someone writing a sense, the ones with most entries first. Matches the words a concept goes
     * by, so "trees" finds tree and "oaks" finds oak.
     *
     * @return Collection<int, ConceptSuggestion>
     */
    public function suggestionsFor(Collection $termKeys, int $limit): Collection
    {
        if ($termKeys->isEmpty()) {
            return collect();
        }

        $conceptIds = ConceptLabel::where(function ($query) use ($termKeys) {
            $termKeys->each(fn (string $key) => $query->orWhere('term_key', 'like', $key.'%'));
        })
            ->distinct()
            ->limit(config('senses.suggestion_candidates'))
            ->pluck('concept_id');

        return Concept::whereIn('id', $conceptIds)
            ->with('synset:id,definition')
            ->get()
            ->map(fn (Concept $concept) => new ConceptSuggestion(
                $concept->id,
                $concept->label,
                (string) $concept->synset?->definition,
                $this->synonymsOf($concept),
                $this->lineageOf($concept),
                $this->entriesUnder([$concept->id], /* inclusive = */ true),
            ))
            ->sortByDesc(fn (ConceptSuggestion $suggestion) => $suggestion->entries)
            ->take($limit)
            ->values();
    }

    /**
     * The other words a concept goes by: WordNet calls one meaning both bungalow and cottage.
     *
     * @return string[]
     */
    private function synonymsOf(Concept $concept): array
    {
        if ($concept->synset_id === null) {
            return [];
        }

        return WordNetSense::where('synset_id', $concept->synset_id)
            ->orderByDesc('tag_count')
            ->limit(config('senses.synonyms') + 1)
            ->pluck('lemma')
            ->reject(fn (string $lemma) => $lemma === mb_strtolower($concept->label))
            ->take(config('senses.synonyms'))
            ->values()
            ->all();
    }

    /**
     * What a concept is a kind of, nearest first, as far up as is worth showing.
     *
     * @return string[]
     */
    private function lineageOf(Concept $concept, int $depth = 2): array
    {
        $lineage = [];
        for ($parent = $concept->parent; $parent !== null && count($lineage) < $depth; $parent = $parent->parent) {
            $lineage[] = $parent->label;
        }

        return $lineage;
    }

    /**
     * The concepts a word names. WordNet's name for a meaning is often no word of the dictionary — nothing is
     * glossed "bungalow", though a Quenya word means one — so a search for it can only be answered this way.
     *
     * @param  Collection<int, string>  $termKeys
     * @return Collection<int, int> concept IDs
     */
    public function conceptIdsForLabel(Collection $termKeys): Collection
    {
        if ($termKeys->isEmpty()) {
            return collect();
        }

        // no size limit here: a concept this search names is the answer, however much sits under it
        return ConceptLabel::whereIn('term_key', $termKeys)->distinct()->pluck('concept_id');
    }

    /**
     * The kinds of the thing these senses mean, with how many entries each one has. A search for "trees" gets oak,
     * beech and mallorn this way.
     *
     * @param  int[]  $senseIds  as for `subjectConceptIds()`
     * @return Collection<int, RelatedConcept> the ones with the most entries first
     */
    public function narrowerFor(array $senseIds, int $limit): Collection
    {
        $conceptIds = $this->subjectConceptIds($senseIds);
        if ($conceptIds->isEmpty()) {
            return collect();
        }

        return Concept::whereIn('concepts.id', $this->descendantIds($conceptIds))
            ->join('sense_concepts', 'sense_concepts.concept_id', 'concepts.id')
            ->join('lexical_entries', function ($join) {
                $join->on('lexical_entries.sense_id', 'sense_concepts.sense_id')
                    ->where('lexical_entries.is_deleted', 0);
            })
            ->groupBy('concepts.id', 'concepts.label')
            ->orderByRaw('COUNT(lexical_entries.id) DESC')
            ->orderBy('concepts.label')
            ->limit($limit)
            ->get(['concepts.label', DB::raw('COUNT(lexical_entries.id) AS entries')])
            ->map(fn (Concept $concept) => new RelatedConcept($concept->label, (int) $concept->entries));
    }

    /**
     * What the thing these senses mean is a kind of: birch is a kind of tree, of woody plant, of plant. The chain
     * stops where it stops saying anything — an oak is an organism and an entity, but nobody searches that way.
     *
     * @param  int[]  $senseIds  as for `subjectConceptIds()`
     * @return Collection<int, RelatedConcept> the nearest first
     */
    public function broaderFor(array $senseIds, int $limit): Collection
    {
        $conceptIds = $this->subjectConceptIds($senseIds);
        if ($conceptIds->isEmpty()) {
            return collect();
        }

        $ancestors = DB::table('concept_closure')
            ->whereIn('descendant_id', $conceptIds)
            ->where('depth', '>', 0)
            ->orderBy('depth')
            ->distinct()
            ->pluck('ancestor_id');

        $broader = collect();
        foreach ($ancestors as $conceptId) {
            $entries = $this->entriesUnder([$conceptId], /* inclusive = */ true);
            if ($entries > config('senses.max_entries_under_broader') || $broader->count() >= $limit) {
                break;
            }

            $broader->push(new RelatedConcept(Concept::find($conceptId)->label, $entries));
        }

        return $broader;
    }

    /**
     * Everything below these concepts, themselves excluded.
     *
     * @param  Collection<int, int>  $conceptIds
     * @return Collection<int, int>
     */
    private function descendantIds(Collection $conceptIds): Collection
    {
        return DB::table('concept_closure')
            ->whereIn('ancestor_id', $conceptIds)
            ->where('depth', '>', 0)
            ->distinct()
            ->limit(config('senses.max_narrower_concepts'))
            ->pluck('descendant_id');
    }

    /**
     * How many entries there are below these concepts, which is what makes one too broad to widen a search by.
     *
     * @param  int[]  $conceptIds
     */
    private function entriesUnder(array $conceptIds, bool $inclusive = false): int
    {
        $concepts = $this->descendantIds(collect($conceptIds));

        return LexicalEntry::active()
            ->whereIn('sense_id', SenseConcept::whereIn('concept_id', $inclusive ? $concepts->merge($conceptIds) : $concepts)->select('sense_id'))
            ->count();
    }

    /**
     * The concept every sense under this headword already has, when they all have the same one. A new sense glossed
     * "oak" then needs no judgement.
     */
    public function settledForHeadword(string $termKey): ?Concept
    {
        $senseIds = SenseTerm::where('position', 0)->where('term_key', $termKey)->select('sense_id');
        $conceptIds = SenseConcept::whereIn('sense_id', $senseIds)->distinct()->pluck('concept_id');

        return $conceptIds->count() === 1 ? Concept::find($conceptIds->first()) : null;
    }

    /**
     * Puts a sense in front of an editor, replacing any earlier reason it was there for.
     */
    public function review(int $senseId, ConceptReviewReason $reason, ?string $detail = null, ?int $confidence = null): void
    {
        SenseConceptReview::updateOrCreate(
            ['sense_id' => $senseId],
            ['reason' => $reason, 'detail' => $detail === null ? null : mb_substr($detail, 0, 250), 'confidence' => $confidence],
        );
    }

    /**
     * Takes a sense off the editors' list, once it has a concept.
     */
    public function clearReview(int $senseId): void
    {
        SenseConceptReview::where('sense_id', $senseId)->delete();
    }

    /**
     * Replaces what one source assigned to a sense. A concept another source already assigned stays theirs, and a
     * sense with a locked assignment is left alone.
     *
     * @param  Collection<int, ConceptAssignment>  $assignments
     * @return bool whether the sense was updated
     */
    public function assign(int $senseId, ConceptSource $source, Collection $assignments): bool
    {
        if (SenseConcept::where('sense_id', $senseId)->where('is_locked', true)->exists()) {
            return false;
        }

        DB::transaction(function () use ($senseId, $source, $assignments) {
            SenseConcept::where('sense_id', $senseId)->where('source', $source)->delete();
            $assigned = SenseConcept::where('sense_id', $senseId)->pluck('concept_id');

            $assignments
                ->reject(fn (ConceptAssignment $assignment) => $assigned->contains($assignment->concept->id))
                ->each(fn (ConceptAssignment $assignment) => SenseConcept::create([
                    'sense_id' => $senseId,
                    'concept_id' => $assignment->concept->id,
                    'position' => $assignment->position,
                    'source' => $source,
                    'confidence' => $assignment->confidence,
                ]));
        });

        return true;
    }

    /**
     * Rebuilds concept_closure from the concepts' parents: every concept with each of its ancestors, and itself at
     * depth 0. Written through the query builder: it is a few hundred thousand derived rows.
     *
     * @return int the number of rows written
     */
    public function rebuildClosure(): int
    {
        $parents = Concept::pluck('parent_id', 'id');
        $written = 0;
        $buffer = [];

        DB::transaction(function () use ($parents, &$written, &$buffer) {
            DB::table('concept_closure')->delete();

            foreach ($parents->keys() as $id) {
                // the depth cap stops a parent cycle an editor might create from looping forever
                for ($ancestor = $id, $depth = 0; $ancestor !== null && $depth <= self::MAX_DEPTH; $ancestor = $parents[$ancestor], $depth++) {
                    $buffer[] = ['ancestor_id' => $ancestor, 'descendant_id' => $id, 'depth' => $depth];
                }

                // written as we go: every concept's whole lineage at once outgrows a worker's memory
                if (count($buffer) >= self::CHUNK_SIZE) {
                    DB::table('concept_closure')->insert($buffer);
                    $written += count($buffer);
                    $buffer = [];
                }
            }

            if ($buffer !== []) {
                DB::table('concept_closure')->insert($buffer);
                $written += count($buffer);
            }
        });

        return $written;
    }

    /**
     * Keys every word the synset goes by, the way sense terms are keyed, so searches find the concept by any of them.
     */
    private function addLabels(Concept $concept, WordNetSynset $synset): void
    {
        $isVerb = $synset->pos === 'v';
        $synset->senses()->pluck('lemma')
            ->map(fn (string $lemma) => $this->_normalizer->normalize($lemma, $isVerb)->first()?->key)
            ->filter()
            ->unique()
            ->each(fn (string $key) => ConceptLabel::firstOrCreate(['term_key' => $key, 'concept_id' => $concept->id]));
    }
}
