<?php

namespace App\Services\Senses;

use App\Models\Sense;
use App\Repositories\ConceptRepository;
use App\Repositories\Enumerations\ConceptReviewReason;
use App\Repositories\Enumerations\ConceptSource;
use App\Repositories\ValueObjects\ConceptAssignment;
use App\Repositories\WordNetRepository;
use App\Services\Enumerations\ConceptOutcome;
use App\Services\Enumerations\ConceptRelation;
use App\Services\Enumerations\WordNetPos;
use Illuminate\Support\Collection;

/**
 * Turns a judge's answer into concepts. Gemini's answers about contributions and the backfill's answers about the
 * senses that came before it go through here alike, so the same answer always has the same effect.
 */
class ConceptDecisionApplier
{
    public function __construct(
        protected readonly ConceptRepository $_concepts,
        protected readonly WordNetRepository $_wordNet,
        protected readonly SenseClassifier $_classifier,
    ) {}

    /**
     * Assigns what the decision says, or hands the sense to an editor when it can't be used, and reports which.
     *
     * @param  Sense  $sense  with `terms` and `lexical_entries` loaded
     * @param  string[]  $offered  the meanings the judge was shown; an answer naming any other is not trusted
     * @param  int|null  $minimumConfidence  overrides the configured threshold, for replaying decisions that were
     *                                       already vetted under a different one
     */
    public function apply(Sense $sense, ConceptDecision $decision, ConceptSource $source, array $offered = [],
        ?int $minimumConfidence = null): ConceptApplication
    {
        $invented = array_diff($decision->synsetIds, $offered);
        if ($invented !== []) {
            $this->_concepts->review($sense->id, ConceptReviewReason::INVALID_ANSWER, implode(', ', $invented), $decision->confidence);

            return ConceptApplication::review(ConceptReviewReason::INVALID_ANSWER);
        }

        if ($decision->confidence < ($minimumConfidence ?? $this->minimumConfidence())) {
            $this->_concepts->review($sense->id, ConceptReviewReason::UNSURE, $this->describe($decision), $decision->confidence);

            return ConceptApplication::review(ConceptReviewReason::UNSURE);
        }

        if ($decision->synsetIds !== []) {
            return $this->assign($sense, collect($decision->synsetIds)
                ->map(fn (string $synsetId) => new ConceptAssignment($this->_concepts->forSynset($synsetId), 0, $decision->confidence)), $source);
        }

        if ($decision->betterWord !== null) {
            return $this->applyWord($sense, $decision, $source);
        }

        // the judge says this is a name, an untranslated word or a grammatical label
        $this->_concepts->clearReview($sense->id);

        return ConceptApplication::of(ConceptOutcome::NOT_A_CONCEPT);
    }

    /**
     * Hangs the sense on the word the judge offered: on that word's own concept when they mean the same, otherwise
     * on a new concept underneath it.
     */
    private function applyWord(Sense $sense, ConceptDecision $decision, ConceptSource $source): ConceptApplication
    {
        $pos = $this->_classifier->wordNetPos($sense) ?? WordNetPos::cases();
        $synsetId = $this->_wordNet->synsetIdsFor($this->spellings($decision->betterWord), $pos)->first();

        if ($synsetId === null) {
            $this->_concepts->review($sense->id, ConceptReviewReason::UNKNOWN_WORD, $this->describe($decision), $decision->confidence);

            return ConceptApplication::review(ConceptReviewReason::UNKNOWN_WORD);
        }

        $concept = $this->_concepts->forSynset($synsetId);
        if ($decision->relation === ConceptRelation::KIND_OF) {
            $head = $sense->terms->first();
            $concept = $this->_concepts->forWord($head->lemma, $concept, $head->term_key);
        }

        return $this->assign($sense, collect([new ConceptAssignment($concept, 0, $decision->confidence)]), $source);
    }

    /**
     * @param  Collection<int, ConceptAssignment>  $assignments
     */
    private function assign(Sense $sense, Collection $assignments, ConceptSource $source): ConceptApplication
    {
        if (! $this->_concepts->assign($sense->id, $source, $assignments)) {
            return ConceptApplication::of(ConceptOutcome::LOCKED);
        }

        $this->_concepts->clearReview($sense->id);

        return new ConceptApplication(
            ConceptOutcome::ASSIGNED,
            $assignments->map(fn (ConceptAssignment $assignment) => $assignment->concept),
        );
    }

    /**
     * WordNet may write a compound with a space, a hyphen or neither.
     *
     * @return string[]
     */
    private function spellings(string $word): array
    {
        $word = mb_strtolower(trim($word));

        return array_values(array_unique([$word, str_replace('-', ' ', $word), preg_replace('/[\s\-]+/u', '', $word)]));
    }

    /**
     * The answer in one line, so an editor can see what the judge thought.
     */
    private function describe(ConceptDecision $decision): string
    {
        return collect([
            $decision->synsetIds === [] ? null : implode(', ', $decision->synsetIds),
            $decision->betterWord === null ? null : $decision->relation?->value.' '.$decision->betterWord,
        ])->filter()->implode('; ') ?: 'no meaning offered';
    }

    private function minimumConfidence(): int
    {
        return (int) config('ed-senses.minimum_confidence');
    }
}
