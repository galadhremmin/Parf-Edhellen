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
    SearchKeyword,
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
        $entities = $this->_searchIndexRepository->resolveIndexToEntities($groupId, $v);
        return $entities;
    }

    /**
     * HTTP POST. Translates the specified word.
     *
     * @param Request $request
     * @return void
     */
    public function translate(Request $request)
    {
        return $this->entities($request, SearchKeyword::SEARCH_GROUP_DICTIONARY);
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
}
