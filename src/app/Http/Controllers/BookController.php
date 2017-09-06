<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\{ CanTranslateTrait, CanGetTranslationTrait };

class BookController extends Controller
{
    use CanTranslateTrait, CanGetTranslationTrait {
        CanTranslateTrait::__construct insteadof CanGetTranslationTrait;
    }

    public function pageForWord(Request $request, string $word)
    {
        $model = $this->translate($word, 0, true);
        
        return view('book.page', [
            'payload' => $model
        ]);
    }

    public function pageForTranslationId(Request $request, int $id)
    {
        $model = $this->getTranslation($id);
        if (! $model) {
            abort(404);
        }

        return view('book.page', [
            'payload' => $model
        ]);
    }

    public function versions(Request $request, int $id)
    {
        $translations = $this->_translationRepository->getVersions($id);
        if (count($translations) < 1) {
            abort(404);
        }

        $word = $translations[0]->word;
        $model = $this->_bookAdapter->adaptTranslations($translations, [], [], $word, false, false);

        return view('book.version', [
            'word'      => $word,
            'versions'  => $model['sections'][0]['glosses']
        ]);
    }

}
