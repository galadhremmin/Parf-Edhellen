<?php

namespace App\Http\Controllers\Api\v3;

use App\Http\Controllers\Abstracts\Controller;
use App\Models\Sense;
use App\Repositories\Enumerations\ConceptReviewReason;
use App\Services\Enumerations\ConceptOutcome;
use App\Services\Enumerations\ConceptRelation;
use App\Services\Senses\SenseConceptEditor;
use App\Services\Senses\SenseReviewQueue;
use App\Services\Senses\SenseRewording;
use Illuminate\Http\Request;

/**
 * The queue of senses waiting for an editor to say what they mean, and the decisions an editor makes about them.
 */
class SenseReviewApiController extends Controller
{
    public function __construct(
        protected readonly SenseReviewQueue $_queue,
        protected readonly SenseConceptEditor $_editor,
        protected readonly SenseRewording $_rewording,
    ) {}

    /**
     * The next sense to decide, with the meanings it could have, and how many are waiting behind it.
     */
    public function next(Request $request)
    {
        $parameters = $request->validate([
            'reason' => ['sometimes', 'nullable', 'string', 'in:'.$this->reasons()],
            // the senses the editor has passed over in this sitting, so the queue moves on: "12,48,91"
            'skip' => 'sometimes|nullable|string|max:1000',
        ]);

        $reason = ConceptReviewReason::tryFrom((string) ($parameters['reason'] ?? ''));
        $skip = collect(explode(',', (string) ($parameters['skip'] ?? '')))
            ->filter(fn (string $id) => is_numeric($id))
            ->map(fn (string $id) => (int) $id)
            ->take(100)
            ->all();

        return [
            'sense' => $this->_queue->next($skip, $reason),
            'waiting' => $this->_queue->count($reason),
            'by_reason' => $this->_queue->summary(),
        ];
    }

    /**
     * Records what an editor decided: one of the meanings on offer, a meaning they went looking for, or that the
     * sense is no meaning at all.
     */
    public function decide(Request $request, int $id)
    {
        $parameters = $request->validate([
            'synset_id' => 'sometimes|nullable|string|exists:wordnet_synsets,id',
            'concept_id' => 'sometimes|nullable|numeric|exists:concepts,id',
            'relation' => ['sometimes', 'nullable', 'string', 'in:'.implode(',',
                array_column(ConceptRelation::cases(), 'value'))],
            'dismiss' => 'sometimes|boolean',
            // the meanings the page showed, so the log records the offer as well as the answer
            'offered' => 'sometimes|nullable|array|max:32',
            'offered.*' => 'string',
        ]);

        $sense = Sense::with(['word', 'terms'])->findOrFail($id);
        $account = $request->user();
        $offered = array_map('strval', $parameters['offered'] ?? []);
        $relation = ConceptRelation::tryFrom((string) ($parameters['relation'] ?? '')) ?? ConceptRelation::SYNONYM;

        $outcome = match (true) {
            ($parameters['dismiss'] ?? false) == true => $this->_editor->dismiss($sense, $account, $offered),
            ! empty($parameters['synset_id']) => $this->_editor->chooseSynset($sense, $parameters['synset_id'],
                $relation, $account, $offered),
            ! empty($parameters['concept_id']) => $this->_editor->choose($sense, (int) $parameters['concept_id'],
                $account, $offered),
            default => null,
        };

        if ($outcome === null) {
            return response()->json(['message' => 'Nothing was decided: give a meaning, or dismiss the sense.'], 422);
        }

        return [
            'outcome' => $outcome->value,
            'assigned' => $outcome === ConceptOutcome::ASSIGNED,
            'waiting' => $this->_queue->count(),
        ];
    }

    /**
     * Corrects the wording itself, when the sense is no meaning but a mis-transcription: every entry glossed with it
     * moves to the wording it should have had.
     */
    public function reword(Request $request, int $id)
    {
        $parameters = $request->validate([
            'sense' => 'required|string|max:255',
        ]);

        $sense = Sense::with('word')->findOrFail($id);
        $result = $this->_rewording->reword($sense, $parameters['sense'], $request->user());

        if (! $result->wasMoved()) {
            return response()->json(['message' => $result->refusal], 422);
        }

        return [
            'outcome' => ConceptOutcome::REWORDED->value,
            'result' => $result,
            'waiting' => $this->_queue->count(),
        ];
    }

    private function reasons(): string
    {
        return implode(',', array_column(ConceptReviewReason::cases(), 'value'));
    }
}
