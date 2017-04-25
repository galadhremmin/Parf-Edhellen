<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\Translation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\TranslationRepository;
use App\Adapters\BookAdapter;
use App\Helpers\StringHelper;

class BookApiController extends Controller 
{
    private $_translationRepository;
    private $_adapter;

    public function __construct(TranslationRepository $translationRepository, BookAdapter $adapter)
    {
        $this->_translationRepository = $translationRepository;
        $this->_adapter = $adapter;
    }

    public function find(Request $request)
    {
        $this->validate($request, [
            'word'        => 'required',
            'reversed'    => 'boolean',
            'language_id' => 'numeric'
        ]);

        $word       = StringHelper::normalize( $request->input('word') );
        $reversed   = $request->input('reversed') === true;
        $languageId = intval($request->input('language_id'));

        $keywords = $this->_translationRepository->getKeywordsForLanguage($word, $reversed, $languageId);
        return $keywords;
    }

    public function suggest(Request $request) 
    {
        $this->validate($request, [
            'word'        => 'required',
            'language_id' => 'numeric'
        ]);

        $word = StringHelper::normalize( $request->input('word') );
        $languageId = intval($request->input('language_id'));

        $translations = $this->_translationRepository->suggest($word, $languageId); 
        return $translations;
    }

    public function translate(Request $request)
    {
        $this->validate($request, [
            'word' => 'required|max:255'
        ]);

        $word = StringHelper::normalize( $request->input('word') );
        $translations = $this->_translationRepository->getWordTranslations($word);
        $model = $this->_adapter->adaptTranslations($translations, $word);

        return $model;
    }

    public function get(Request $request, int $translationId)
    {
        $translation = $this->_translationRepository->getTranslation($translationId);
        if (! $translation) {
            return response(null, 500);
        }

        return $this->_adapter->adaptTranslations([$translation]);
    }
}