<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Abstracts\Controller;
use App\Repositories\LexicalEntryRepository;
use Illuminate\Http\Request;

class GlossApiController extends Controller
{
    private LexicalEntryRepository $_repository;

    public function __construct(LexicalEntryRepository $repository)
    {
        $this->_repository = $repository;
    }

    public function get(Request $request, int $id)
    {
        $lexicalEntries = $this->_repository->getLexicalEntry($id);
        if ($lexicalEntries->isEmpty()) {
            return response('', 404);
        }

        return [
            'gloss' => $lexicalEntries->first(),
        ];
    }

    public function destroy(Request $request, int $id)
    {
        $data = $request->validate([
            'replacement_id' => 'numeric|not_in:'.$id,
        ]);

        $replacementId = intval($data['replacement_id']);

        if ($replacementId !== 0) {
            $lexicalEntryVersions = $this->_repository->getLexicalEntryVersions($replacementId)->getVersions();
            if ($lexicalEntryVersions->count() === 0) {
                return response(null, 400);
            }
        }

        return response(null,
            $this->_repository->deleteLexicalEntryWithId($id, $replacementId) ? 200 : 400
        );
    }

    /**
     * HTTP POST. Suggests glosses for the specified array of words.
     *
     * @return void
     */
    public function suggest(Request $request)
    {
        $this->validate($request, [
            'words' => 'required|array',
            'language_id' => 'numeric',
            'inexact' => 'boolean',
        ]);

        $words = $request->input('words');
        $languageId = intval($request->input('language_id'));
        $inexact = boolval($request->input('inexact'));

        return $this->_repository->suggest($words, $languageId, $inexact);
    }
}
