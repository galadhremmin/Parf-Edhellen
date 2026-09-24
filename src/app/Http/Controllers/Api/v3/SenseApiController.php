<?php

namespace App\Http\Controllers\Api\v3;

use App\Http\Controllers\Abstracts\Controller;
use App\Repositories\ConceptRepository;
use App\Repositories\SenseTermRepository;
use Illuminate\Http\Request;

class SenseApiController extends Controller
{
    public function __construct(
        protected readonly ConceptRepository $_conceptRepository,
        protected readonly SenseTermRepository $_senseTermRepository,
    ) {}

    /**
     * Offers what a sense might mean, for the form that writes one: the meanings the taxonomy knows, and the wordings
     * the dictionary already glosses words with.
     */
    public function find(Request $request)
    {
        $parameters = $request->validate([
            'q' => 'sometimes|nullable|string|max:64',
            // with a meaning given, the wordings offered are the ones that already mean it
            'concept_id' => 'sometimes|nullable|numeric|exists:concepts,id',
        ]);

        $query = trim((string) ($parameters['q'] ?? ''));
        $conceptId = isset($parameters['concept_id']) ? intval($parameters['concept_id']) : null;

        if ($query === '' && $conceptId === null) {
            return ['concepts' => [], 'senses' => []];
        }

        return [
            'concepts' => $query === '' ? [] : $this->_conceptRepository->suggestionsFor(
                $this->_senseTermRepository->keysFor($query), config('ed-senses.suggestions')
            ),
            'senses' => $this->_senseTermRepository->suggestionsFor($query, config('ed-senses.suggestions'), $conceptId),
        ];
    }
}
