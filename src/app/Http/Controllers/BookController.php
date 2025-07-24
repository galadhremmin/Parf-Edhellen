<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Abstracts\BookBaseController;
use App\Http\Controllers\Traits\CanGetLanguage;
use App\Models\LexicalEntryGroup;
use App\Models\SearchKeyword;
use App\Repositories\ValueObjects\ExternalEntitySearchValue;
use App\Repositories\ValueObjects\SpecificEntitiesSearchValue;
use Illuminate\Http\Request;

class BookController extends BookBaseController
{
    use CanGetLanguage;

    public function pageForWord(Request $request, string $word, ?string $language = null)
    {
        return $this->pageForEntity($request, '', SearchKeyword::SEARCH_GROUP_DICTIONARY, $word, $language);
    }

    public function pageForEntity(Request $request, string $groupName, string $groupId, string $word, ?string $language = null)
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
            'payload' => $entities,
        ]);
    }

    public function pageForGlossId(Request $request, int $id)
    {
        $v = new SpecificEntitiesSearchValue([$id]);
        $entities = $this->_searchIndexRepository->resolveIndexToEntities(SearchKeyword::SEARCH_GROUP_DICTIONARY, $v);
        if (count($entities['entities']['sections']) === 0) {
            abort(404);
        }

        return view('book.page', [
            'payload' => $entities,
        ]);
    }

    public function pageForExternalSource(Request $request, int $lexicalEntryGroupId, string $lexicalEntryGroupName, string $externalId)
    {
        $v = new ExternalEntitySearchValue([
            'external_id' => $externalId,
        ]);
        $entities = $this->_searchIndexRepository->resolveIndexToEntities(SearchKeyword::SEARCH_GROUP_DICTIONARY, $v);
        if (count($entities['entities']['sections']) === 0) {
            $lexicalEntryGroup = LexicalEntryGroup::findOrFail($lexicalEntryGroupId);

            // Don't navigate the client to another website destination if they want to be taken back.
            $url = config('app.url');
            $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $url;
            if (parse_url($referer, PHP_URL_HOST) !== parse_url($url, PHP_URL_HOST)) {
                $referer = $url;
            }

            return view('book.not-found-external', [
                'external_url' => str_replace('{ExternalID}', $externalId, $lexicalEntryGroup->external_link_format),
                'lexical_entry_group' => $lexicalEntryGroup,
                'referer' => $referer,
            ]);
        }

        return view('book.page', [
            'payload' => $entities,
        ]);
    }

    public function redirectToLatest(Request $request, int $id)
    {
        $glosses = $this->getGlossUnadapted($id);
        if ($glosses->count() < 1) {
            abort(404);
        }

        return redirect()->route('gloss.ref', ['id' => $glosses->first()->id]);
    }

    public function versions(Request $request, int $id)
    {
        $lexicalEntries = $this->_lexicalEntryRepository->getLexicalEntryVersions($id);
        if ($lexicalEntries->getVersions()->count() < 1) {
            abort(404);
        }

        $model = $this->_bookAdapter->adaptLexicalEntryVersions($lexicalEntries->getVersions(), $lexicalEntries->getLatestVersionId());

        return view('book.version', $model + [
            'user' => $request->user(),
        ]);
    }
}
