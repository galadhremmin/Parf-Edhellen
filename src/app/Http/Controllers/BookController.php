<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Language;
use App\Http\Controllers\Abstracts\BookBaseController;
use App\Http\Controllers\Traits\{
    CanGetLanguage
};

class BookController extends BookBaseController
{
    use CanGetLanguage;

    public function pageForWord(Request $request, string $word, string $language = null)
    {
        $this->validateGetGlossConfiguration($request);

        $language     = $this->getLanguageByShortName($language);
        $includeOld   = $request->has('include_old')     ? $request->input('include_old') : true;
        $glossGroupId = $request->has('gloss_group_ids') ? $request->input('gloss_group_ids') : null;
        $speechIds    = $request->has('speech_ids')      ? $request->input('speech_ids') : null;

        $model = $this->findGlosses($word, $language ? $language->id : 0, true, $includeOld, $speechIds, $glossGroupId);

        return view('book.page', [
            'payload' => $model
        ]);
    }

    public function pageForGlossId(Request $request, int $id)
    {
        $model = $this->getGloss($id);
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
