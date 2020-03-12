<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Language;
use App\Http\Controllers\Traits\{
    CanGetLanguage,
    CanTranslate, 
    CanGetGloss
};

class BookController extends Controller
{
    use CanTranslate, CanGetGloss, CanGetLanguage {
        CanTranslate::__construct insteadof CanGetGloss;
    } // ;

    public function pageForWord(Request $request, string $word, string $language = null)
    {
        $language = $this->getLanguageByShortName($language);
        $model = $this->translate($word, $language ? $language->id : 0, true);
        
        return view('book.page', [
            'payload' => $model
        ]);
    }

    public function pageForGlossId(Request $request, int $id)
    {
        $model = $this->getGlossVersion($id);
        if (! $model) {
            abort(404);
        }

        return view('book.page', [
            'payload' => $model
        ]);
    }

    public function redirectToLatest(Request $request, int $id)
    {
        $gloss = $this->getGlossUnadapted($id, true);
        if (! $gloss) {
            abort(404);
        }

        return redirect()->route('gloss.ref', ['id' => $gloss->first()->id]);
    }

    public function versions(Request $request, int $id)
    {
        $glosses = $this->_glossRepository->getVersions($id);
        if (count($glosses) < 1) {
            abort(404);
        }

        $word = $glosses[0]->word;
        $model = $this->_bookAdapter->adaptGlosses($glosses, [], [], $word, false, false);

        return view('book.version', [
            'word'      => $word,
            'versions'  => $model['sections'][0]['glosses']
        ]);
    }
}
