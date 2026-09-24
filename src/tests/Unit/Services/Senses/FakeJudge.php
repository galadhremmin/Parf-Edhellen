<?php

namespace Tests\Unit\Services\Senses;

use App\Interfaces\IJudgesSenseConcepts;
use App\Services\Senses\ConceptDecision;
use App\Services\Senses\ConceptDecisionRequest;
use Illuminate\Support\Collection;

/**
 * Answers with prepared decisions, and records what it was asked, so a test can check that the cheaper steps of the
 * resolver never reach a judge.
 */
class FakeJudge implements IJudgesSenseConcepts
{
    /** @var Collection<int, ConceptDecisionRequest> */
    public Collection $asked;

    /** @var array<int, array<string, mixed>> keyed by sense ID, in the answer schema's shape */
    private array $_answers = [];

    public function __construct()
    {
        $this->asked = collect();
    }

    /**
     * @param  string[]  $synsetIds
     */
    public function willAnswer(int $senseId, array $synsetIds = [], ?string $betterWord = null, ?string $relation = null, int $confidence = 90): self
    {
        $this->_answers[$senseId] = [
            'sense_id' => $senseId,
            'synset_ids' => $synsetIds,
            'better_word' => $betterWord,
            'relation' => $relation,
            'confidence' => $confidence,
        ];

        return $this;
    }

    public function name(): string
    {
        return 'fake judge';
    }

    public function decide(Collection $requests): Collection
    {
        $this->asked = $this->asked->merge($requests);

        return $requests
            ->flatMap(fn (ConceptDecisionRequest $request) => $request->senses)
            ->map(fn ($sense) => $this->_answers[$sense->senseId] ?? null)
            ->filter()
            ->map(fn (array $answer) => ConceptDecision::fromAnswer($answer))
            ->values();
    }
}
