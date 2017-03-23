<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\TranslationRepository;

class BookApiController extends Controller 
{
    private $_translationRepository;

    public function __construct(TranslationRepository $translationRepository)
    {
        $this->_translationRepository = $translationRepository;
    }

    public function find(Request $request)
    {
        $word       = $request->input('word');
        $reversed   = $request->input('reversed') === true;
        $languageId = intval($request->input('languageId'));

        $keywords = $this->_translationRepository->getKeywordsForLanguage($word, $reversed, $languageId);
        return $keywords;
    }

}