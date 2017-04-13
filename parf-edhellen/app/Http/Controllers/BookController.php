<?php

namespace App\Http\Controllers;

use App\Adapters\BookAdapter;
use Illuminate\Http\Request;
use App\Repositories\TranslationRepository;

class BookController extends Controller
{
    private $_translationRepository;
    private $_adapter;

    public function __construct(TranslationRepository $translationRepository, BookAdapter $adapter)
    {
        $this->_translationRepository = $translationRepository;
        $this->_adapter = $adapter;
    }

    public function pageForWord(Request $request, string $word)
    {
        $translations = $this->_translationRepository->getWordTranslations($word);
        $model = $this->_adapter->adaptTranslations($translations, $word);

        return view('book.page', $model);
    }

    public function pageForTranslationId(Request $request, int $id)
    {
        $translation = $this->_translationRepository->getTranslation($id);
        $model = $this->_adapter->adaptTranslations([ $translation ], null);

        return view('book.page', $model);
    }

}

