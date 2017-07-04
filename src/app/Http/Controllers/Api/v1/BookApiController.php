<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;

use App\Models\{ Translation, TranslationGroup, Word };
use App\Http\Controllers\Controller;
use App\Repositories\{ TranslationRepository, SentenceRepository };
use App\Adapters\BookAdapter;
use App\Helpers\StringHelper;

class BookApiController extends Controller 
{
    private $_translationRepository;
    private $_sentenceRepository;
    private $_adapter;

    public function __construct(TranslationRepository $translationRepository, 
        SentenceRepository $sentenceRepository, BookAdapter $adapter)
    {
        $this->_translationRepository = $translationRepository;
        $this->_sentenceRepository = $sentenceRepository;
        $this->_adapter = $adapter;
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
            'reversed'    => 'boolean',
            'language_id' => 'numeric'
        ]);

        $word       = StringHelper::normalize( $request->input('word'), /* accentsMatter: */ false );
        $reversed   = $request->input('reversed') === true;
        $languageId = intval($request->input('language_id'));

        $keywords = $this->_translationRepository->getKeywordsForLanguage($word, $reversed, $languageId);
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
            'inflections' => 'sometimes|boolean'
        ]);

        $word = StringHelper::normalize( $request->input('word') );
        $languageId = $request->has('language_id') ? intval($request->input('language_id')) : 0;
        
        $translations = $this->_translationRepository->getWordTranslations($word, $languageId);

        $inflections = $request->has('inflections') && $request->input('inflections')
            ? $this->_sentenceRepository->getInflectionsForTranslations(array_map(function ($v) {
                return $v->id;
            }, $translations)) : [];

        $model = $this->_adapter->adaptTranslations($translations, $word, $inflections);
        return $model;
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
        $translation = $this->_translationRepository->getTranslation($translationId);
        if (! $translation) {
            return response(null, 404);
        }

        return $this->_adapter->adaptTranslations([$translation]);
    }
}