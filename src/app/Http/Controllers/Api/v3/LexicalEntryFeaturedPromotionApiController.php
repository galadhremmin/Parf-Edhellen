<?php

namespace App\Http\Controllers\Api\v3;

use App\Http\Controllers\Abstracts\Controller;
use App\Repositories\LexicalEntryFeaturedPromotionRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LexicalEntryFeaturedPromotionApiController extends Controller
{
    private LexicalEntryFeaturedPromotionRepository $_repository;

    public function __construct(LexicalEntryFeaturedPromotionRepository $repository)
    {
        $this->_repository = $repository;
    }

    /**
     * Records that the authenticated user chose to feature a different lexical entry than
     * the one the ranking algorithm picked, for a given search word and language. Purely a
     * logging endpoint for analytics — the featured swap itself happens client-side.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'search_word' => 'required|string|max:191',
            'language_id' => 'required|integer|exists:languages,id',
            'lexical_entry_id' => 'required|integer|exists:lexical_entries,id',
            'previous_lexical_entry_id' => 'nullable|integer|exists:lexical_entries,id',
        ]);

        $this->_repository->record(
            $request->user()->id,
            $data['search_word'],
            $data['language_id'],
            $data['lexical_entry_id'],
            $data['previous_lexical_entry_id'] ?? null,
        );

        return response()->json(null, 201);
    }
}
