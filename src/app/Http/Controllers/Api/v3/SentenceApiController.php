<?php

namespace App\Http\Controllers\Api\v3;

use App\Http\Controllers\Abstracts\Controller;
use App\Models\SentenceFragment;
use App\Repositories\SentenceRepository;
use Illuminate\Http\Request;

class SentenceApiController extends Controller
{
    private SentenceRepository $_repository;

    public function __construct(SentenceRepository $repository)
    {
        $this->_repository = $repository;
    }

    public function show(Request $request, int $id)
    {
        $sentence = $this->_repository->getSentence($id);
        if (! $sentence) {
            return response(null, 404);
        }

        return $sentence;
    }

    public function suggestFragments(Request $request)
    {
        $suggestionMap = $request->validate([
            'language_id' => 'required|numeric|exists:languages,id',
            'fragment' => 'required|string',
        ]);

        $languageId = intval($suggestionMap['language_id']);
        $suggestions = $this->_repository->suggestFragmentGlosses(collect([
            new SentenceFragment([
                'fragment' => $suggestionMap['fragment'],
                'type' => 0,
                'gloss_id' => 0,
            ]),
        ]), $languageId);

        return $suggestions;
    }
}
