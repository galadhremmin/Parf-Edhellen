<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\TranslationRepository;
use App\Adapters\BookAdapter;

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
        $word       = $request->input('word');
        $reversed   = $request->input('reversed') === true;
        $languageId = intval($request->input('languageId'));

        $keywords = $this->_translationRepository->getKeywordsForLanguage($word, $reversed, $languageId);
        return $keywords;
    }

    public function translate(Request $request, string $word)
    {
        $translations = $this->_translationRepository->getWordTranslations($word);
        $model = $this->_adapter->adaptTranslations($translations, $word);

        return $model;
    }
}