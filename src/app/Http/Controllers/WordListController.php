<?php

namespace App\Http\Controllers;

use App\Adapters\WordListAdapter;
use App\Http\Controllers\Abstracts\Controller;
use App\Models\WordList;
use App\Services\Flashcards\FlashcardDirection;
use Illuminate\Http\Request;

class WordListController extends Controller
{
    private WordListAdapter $_adapter;

    public function __construct(WordListAdapter $adapter)
    {
        $this->_adapter = $adapter;
    }

    /**
     * Lists the word lists belonging to the signed in account.
     */
    public function index(Request $request)
    {
        $account = $request->user();

        $wordLists = WordList::forAccount($account)
            ->withCount('lexical_entries')
            ->orderBy('name')
            ->get();

        return view('word-list.index', [
            'wordLists' => $this->_adapter->adaptMany($wordLists, $account->id),
        ]);
    }

    /**
     * Displays a single word list. Accessible to guests when the list is public.
     */
    public function show(Request $request, int $id, ?string $name = null)
    {
        $wordList = $this->findViewableOrFail($request, $id);

        // Redirect to the canonical URL when the name is missing or stale, so that a renamed list
        // does not accumulate several addresses in search engines.
        $canonicalName = $this->_adapter->toSeoName($wordList->name);
        if ($name !== $canonicalName) {
            return redirect()->route('word-list.show', [
                'id' => $wordList->id,
                'name' => $canonicalName,
            ]);
        }

        return view('word-list.show', [
            'wordList' => $wordList,
            'canEdit' => $this->canEdit($request, $wordList),
        ]);
    }

    /**
     * Turns a word list into a deck of flashcards.
     *
     * The deck itself is dealt by the API; this only renders the shell the study app mounts into.
     */
    public function study(Request $request, int $id)
    {
        $wordList = $this->findViewableOrFail($request, $id);

        return view('word-list.study', [
            'wordList' => $wordList,
            'direction' => FlashcardDirection::parse($request->query('direction'))->value,
        ]);
    }

    /**
     * Retrieves the word list, provided that the requester is allowed to see it.
     */
    private function findViewableOrFail(Request $request, int $id): WordList
    {
        $wordList = WordList::findOrFail($id);

        if (! $wordList->is_public && ! $this->canEdit($request, $wordList)) {
            // 404 rather than 403: the existence of a private list is itself private.
            abort(404);
        }

        return $wordList;
    }

    private function canEdit(Request $request, WordList $wordList): bool
    {
        $account = $request->user();

        return $account !== null && $wordList->account_id === $account->id;
    }
}
