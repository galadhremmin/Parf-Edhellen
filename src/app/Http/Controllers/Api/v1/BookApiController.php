<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;

use App\Traits\{ CanTranslateTrait, CanGetTranslationTrait };
use App\Models\{ Translation, TranslationGroup, Word, ForumContext, Keyword };
use App\Http\Controllers\Controller;
use App\Helpers\StringHelper;

class BookApiController extends Controller 
{
    use CanTranslateTrait, CanGetTranslationTrait { 
        CanTranslateTrait::__construct insteadof CanGetTranslationTrait;
        CanTranslateTrait::translate as protected doTranslate; 
    }

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

    /**
     * HTTP GET. Gets all available translation groups.
     *
     * @param Request $request
     * @return void
     */
    public function getGroups(Request $request)
    {
        return TranslationGroup::orderBy('name')->get();
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

        if ($request->has('max')) {
            $query = $query->take($request->input('max'));
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
        $this->validate($request, [
            'word'        => 'required',
            'include_old' => 'required|boolean',
            'reversed'    => 'boolean',
            'language_id' => 'numeric',
        ]);

        $word       = StringHelper::normalize( $request->input('word'), /* accentsMatter: */ false, /* retainWildcard: */ true );
        $includeOld = boolval($request->input('include_old'));
        $reversed   = $request->input('reversed') === true;
        $languageId = intval($request->input('language_id'));

        $keywords = $this->_translationRepository->getKeywordsForLanguage($word, $reversed, $languageId, $includeOld);
        return $keywords;
    }

    /**
     * HTTP POST. Suggests glosses for the specified array of words. 
     *
     * @param Request $request
     * @return void
     */
    public function suggest(Request $request) 
    {
        $this->validate($request, [
            'words'       => 'required|array',
            'language_id' => 'numeric',
            'inexact'     => 'boolean'
        ]);

        $words = $request->input('words');
        $languageId = intval($request->input('language_id'));
        $inexact = boolval($request->input('inexact'));
        
        return $this->_translationRepository->suggest($words, $languageId, $inexact); 
    }

    /**
     * HTTP POST. Translates the specified word.
     *
     * @param Request $request
     * @return void
     */
    public function translate(Request $request)
    {
        $this->validate($request, [
            'word'        => 'required|max:255',
            'language_id' => 'sometimes|required|exists:languages,id',
            'include_old' => 'sometimes|required|boolean',
            'inflections' => 'sometimes|boolean'
        ]);

        $word = StringHelper::normalize( $request->input('word') );
        $languageId = $request->has('language_id') ? intval($request->input('language_id')) : 0;
        $includeOld = $request->has('include_old') ? boolval($request->input('include_old')) : true;
        $inflections = $request->has('inflections') && $request->input('inflections');

        return $this->doTranslate($word, $languageId, $inflections, $includeOld);
    }

    /**
     * HTTP GET. Gets the gloss corresponding to the specified ID.
     *
     * @param Request $request
     * @param int $translationId
     * @return void
     */
    public function get(Request $request, int $translationId)
    {
        $translation = $this->getTranslation($translationId);
        if (! $translation) {
            return response(null, 404);
        }

        return $translation;
    }
}
