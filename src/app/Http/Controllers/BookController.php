<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SearchKeyword;
use App\Http\Controllers\Abstracts\BookBaseController;
use App\Http\Controllers\Traits\CanGetLanguage;
use App\Repositories\ValueObjects\SpecificEntitiesSearchValue;

class BookController extends BookBaseController
{
    use CanGetLanguage;

    public function pageForWord(Request $request, string $word, string $language = null)
    {
        return $this->pageForEntity($request, '', SearchKeyword::SEARCH_GROUP_DICTIONARY, $word, $language);
    }

    public function pageForEntity(Request $request, string $groupName, string $groupId, string $word, string $language = null)
    {
        $languageId = 0;
        if ($language !== null) {
            $language = $this->getLanguageByShortName($language);
            if ($language !== null) {
                $languageId = $language->id;
            }
        }

        $v = $this->validateFindRequest($request, ['word' => $word, 'language_id' => $languageId]);
        $entities = $this->_searchIndexRepository->resolveIndexToEntities($groupId, $v);

        return view('book.page', [
            'payload' => $entities
        ]);
    }

    public function pageForGlossId(Request $request, int $id)
    {
        $v = new SpecificEntitiesSearchValue([$id]);
        $entities = $this->_searchIndexRepository->resolveIndexToEntities(SearchKeyword::SEARCH_GROUP_DICTIONARY, $v);
        if (count($entities['entities']) === 0) {
            abort(404);
        }

        return view('book.page', [
            'payload' => $entities
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
            'versions'  => $model['sections'][0]['entities']
        ]);
    }
}
