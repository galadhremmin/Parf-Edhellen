<?php

namespace App\Interfaces;

use App\Services\Senses\ConceptDecision;
use App\Services\Senses\ConceptDecisionRequest;
use Illuminate\Support\Collection;

/**
 * Answers which WordNet meaning a sense has, when the rules can't tell. An interface so that the resolver can be
 * tested, and so the same request can be put to a model or to a person.
 */
interface IJudgesSenseConcepts
{
    /**
     * Who is answering, for the audit log: a model and its version, or the name of a person.
     */
    public function name(): string;

    /**
     * @param  Collection<int, ConceptDecisionRequest>  $requests
     * @return Collection<int, ConceptDecision> one per sense; empty when nobody could answer
     */
    public function decide(Collection $requests): Collection;
}
