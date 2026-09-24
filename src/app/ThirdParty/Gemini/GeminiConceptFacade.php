<?php

namespace App\ThirdParty\Gemini;

use App\Interfaces\IJudgesSenseConcepts;
use App\Services\Senses\ConceptDecision;
use App\Services\Senses\ConceptDecisionPrompt;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class GeminiConceptFacade extends AbstractGeminiFacade implements IJudgesSenseConcepts
{
    public function __construct(protected readonly ConceptDecisionPrompt $_prompt) {}

    public function name(): string
    {
        return 'gemini/'.config('gemini.model');
    }

    /**
     * Asks Gemini which meaning each sense has. Answers nothing when the judge is switched off, has no key, or the
     * call fails: the senses then wait for an editor rather than losing their turn.
     */
    public function decide(Collection $requests): Collection
    {
        $apiKey = config('gemini.api_key', '');
        if (! config('senses.judge') || empty($apiKey) || $requests->isEmpty()) {
            return collect();
        }

        try {
            $raw = $this->_callGemini($apiKey, $this->_prompt->prompt($requests), schema: $this->_prompt->schema());
            $decisions = json_decode($raw, true);

            if (! isset($decisions['decisions']) || ! is_array($decisions['decisions'])) {
                throw new \RuntimeException(sprintf('Gemini did not answer with decisions: %s', $raw));
            }

            return collect($decisions['decisions'])->map(fn (array $answer) => ConceptDecision::fromAnswer($answer));
        } catch (\Throwable $e) {
            Log::warning('GeminiConceptFacade: failed to judge senses, leaving them unassigned.', [
                'error' => $e->getMessage(),
                'senses' => $requests->flatMap(fn ($request) => $request->senses)->pluck('senseId')->all(),
            ]);

            return collect();
        }
    }
}
