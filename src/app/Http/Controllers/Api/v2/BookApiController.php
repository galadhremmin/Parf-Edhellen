<?php

namespace App\Http\Controllers\Api\v2;

use Illuminate\Http\Request;
use Cache;

use App\Http\Controllers\Abstracts\BookBaseController;
use App\Helpers\StringHelper;
use App\Repositories\ValueObjects\SearchIndexSearchValue;
use App\Models\{
    Keyword,
    Gloss, 
    GlossGroup, 
    Language,
    Word
};

class BookApiController extends BookBaseController
{
    /**
     * HTTP GET. Gets the word which corresponds to the specified ID. 
     *
     * @param Request $request
     * @param int $id
     * @return void
     */
    public function getWord(Request $request, int $id)
    {
        $word = Word::find($id);
        if (! $word) {
            return response(null, 404);
        }

        return $word;
    }

    public function getLanguages()
    {
        $languages = Cache::remember('ed.languages', 60 * 60 /* seconds */, function () {
            return Language::all()
                ->sortBy('order')
                ->sortBy('name')
                ->groupBy('category')
                ->toArray();
        });

        return $languages;
    }

    /**
     * HTTP GET. Gets all available gloss groups.
     *
     * @param Request $request
     * @return void
     */
    public function getGroups(Request $request)
    {
        return GlossGroup::orderBy('name')->get();
    }

    /**
     * HTTP POST. Performs a forward search among words for the specified word parameter.
     *
     * @param Request $request
     * @return void
     */
    public function findWord(Request $request) 
    {
        $this->validate($request, [
            'word' => 'required|string|max:64',
            'max'  => 'sometimes|numeric|min:1'
        ]);

        $normalizedWord = StringHelper::normalize( $request->input('word') );
        $max = intval( $request->input('max') );

        $query = Word::where('normalized_word', 'like', $normalizedWord.'%');

        if ($max > 0) {
            $query = $query->take($max);
        }

        return $query->select('id', 'word')->get();
    }

    /**
     * HTTP POST. Finds keywords for the specified word.
     *
     * @param Request $request
     * @return void
     */
    public function find(Request $request)
    {
        $v = $this->validateFindRequest($request);
        return $this->_searchIndexRepository->findKeywords($v);
    }

    /**
     * HTTP POST. Finds entitites corresponding to a specified keyword.
     */
    public function entities(Request $request, int $groupId)
    {
        $v = $this->validateFindRequest($request);
        return $this->_searchIndexRepository->resolveIndexToEntities($groupId, new SearchIndexSearchValue([
            'word' => $v->getWord()
        ]));
    }

    /**
     * HTTP POST. Translates the specified word.
     *
     * @param Request $request
     * @return void
     */
    public function translate(Request $request)
    {
        $this->validateBasicRequest($request, [
            'inflections' => 'sometimes|boolean'
        ]);

        $glossGroupIds = $request->has('gloss_group_ids') ? $request->input('gloss_group_ids') : null;
        $includeOld = $request->has('include_old') ? boolval($request->input('include_old')) : true;
        $inflections = $request->has('inflections') && $request->input('inflections');
        $languageId = $request->has('language_id') ? intval($request->input('language_id')) : 0;
        $speechIds = $request->has('speech_ids') ? $request->input('speech_ids') : null;
        $word = StringHelper::normalize( $request->input('word') );

        return $this->findGlosses($word, $languageId, $inflections, $includeOld, $speechIds, $glossGroupIds);
    }

    /**
     * HTTP GET. Gets the gloss corresponding to the specified ID.
     *
     * @param Request $request
     * @param int $glossId
     * @return void
     */
    public function get(Request $request, int $glossId)
    {
        $gloss = $this->getGloss($glossId);
        if (! $gloss) {
            return response(null, 404);
        }

        return $gloss;
    }

    private function validateBasicRequest(Request $request, array $additional = [])
    {
        $this->validateGetGlossConfiguration($request);
        $this->validate($request, $additional + [
            'word' => 'required|min:1|max:255',
        ]);
    }

    private function validateFindRequest(Request $request): SearchIndexSearchValue
    {
        $v = $request->validate([
            'gloss_group_ids'   => 'sometimes|array',
            'gloss_group_ids.*' => 'sometimes|numeric',
            'include_old'       => 'sometimes|boolean',
            'language_id'       => 'sometimes|numeric',
            'reversed'          => 'sometimes|boolean',
            'speech_ids'        => 'sometimes|array',
            'speech_ids.*'      => 'sometimes|numeric',
            'word'              => 'required|string'
        ]);

        $glossGroupIds = isset($v['gloss_group_ids']) ? $v['gloss_group_ids'] : null;
        $includeOld    = isset($v['include_old']) ? boolval($v['include_old']) : true;
        $languageId    = isset($v['language_id']) ? intval($v['language_id']) : null;
        $reversed      = isset($v['reversed']) ? boolval($v['reversed']) : false;
        $speechIds     = isset($v['speech_ids']) ? $v['speech_ids'] : null;
        $word          = StringHelper::normalize($v['word'], /* accentsMatter: */ false, /* retainWildcard: */ true);

        return new SearchIndexSearchValue([
            'gloss_group_ids' => $glossGroupIds,
            'include_old'     => $includeOld,
            'language_id'     => $languageId,
            'reversed'        => $reversed,
            'speech_ids'      => $speechIds,
            'word'            => $word
        ]);
    }
}
