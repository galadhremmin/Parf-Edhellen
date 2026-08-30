<?php

namespace App\Http\Controllers\Api\v3;

use App\Adapters\WordListAdapter;
use App\Http\Controllers\Abstracts\Controller;
use App\Models\WordList;
use App\Models\WordListEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WordListApiController extends Controller
{
    private WordListAdapter $_adapter;

    public function __construct(WordListAdapter $adapter)
    {
        $this->_adapter = $adapter;
    }

    /**
     * Get all word lists for the authenticated user.
     *
     * When an optional ?lexical_entry_id=N query parameter is provided,
     * each list gains a `contains_entry` count (0 or 1) indicating
     * whether it already holds that specific entry.
     */
    public function index(Request $request): JsonResponse
    {
        $query = WordList::forAccount($request->user())
            ->withCount('lexical_entries')
            ->orderBy('name');

        if ($entryId = $request->input('lexical_entry_id')) {
            $query->withCount(['lexical_entries as contains_entry' => function ($q) use ($entryId) {
                $q->where('lexical_entries.id', (int) $entryId);
            }]);
        }

        return response()->json([
            'word_lists' => $this->_adapter->adaptMany($query->get(), $request->user()->id),
        ]);
    }

    /**
     * Get a specific word list with its entries
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $account = $request->user();

        $wordList = WordList::where(function ($query) use ($account) {
            $query->where('is_public', true);

            if ($account !== null) {
                $query->orWhere('account_id', $account->id);
            }
        })
            ->with(['lexical_entries' => function ($query) {
                // Deleted and rejected entries stay out of the view: the dictionary no longer shows
                // them, so a word list should not either.
                $query->where('lexical_entries.is_deleted', 0)
                    ->where('lexical_entries.is_rejected', 0)
                    ->with(['word', 'language', 'speech', 'glosses'])
                    ->orderBy('word_list_entries.order')
                    ->orderBy('word_list_entries.created_at');
            }])
            ->findOrFail($id);

        return response()->json([
            'word_list' => $this->_adapter->adapt($wordList, $account?->id),
        ]);
    }

    /**
     * Create a new word list
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:128',
            'description' => 'nullable|string|max:1000',
            'is_public' => 'boolean',
        ]);

        $wordList = WordList::create([
            'account_id' => $request->user()->id,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_public' => $data['is_public'] ?? false,
        ]);

        return response()->json([
            'word_list' => $this->_adapter->adaptSummary($wordList, $request->user()->id),
        ], 201);
    }

    /**
     * Update a word list
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $wordList = WordList::forAccount($request->user())
            ->findOrFail($id);

        $data = $request->validate([
            'name' => 'sometimes|string|max:128',
            'description' => 'nullable|string|max:1000',
            'is_public' => 'sometimes|boolean',
        ]);

        $wordList->update($data);

        return response()->json([
            'word_list' => $this->_adapter->adaptSummary($wordList, $request->user()->id),
        ]);
    }

    /**
     * Delete a word list
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $wordList = WordList::forAccount($request->user())
            ->findOrFail($id);

        $wordList->delete();

        return response()->json(null, 204);
    }

    /**
     * Add lexical entry to word list
     */
    public function addEntry(Request $request, int $wordListId): JsonResponse
    {
        $wordList = WordList::forAccount($request->user())
            ->findOrFail($wordListId);

        $data = $request->validate([
            'lexical_entry_id' => 'required|integer|exists:lexical_entries,id',
            'order' => 'nullable|integer',
        ]);

        // Check if entry already exists in word list
        if ($wordList->lexical_entries()->where('lexical_entry_id', $data['lexical_entry_id'])->exists()) {
            return response()->json([
                'error' => 'Entry already exists in word list',
            ], 400);
        }

        $wordList->lexical_entries()->attach($data['lexical_entry_id'], [
            'order' => $data['order'] ?? null,
        ]);

        return response()->json([
            'message' => 'Entry added to word list',
        ], 201);
    }

    /**
     * Remove lexical entry from word list
     */
    public function removeEntry(Request $request, int $wordListId, int $entryId): JsonResponse
    {
        $wordList = WordList::forAccount($request->user())
            ->findOrFail($wordListId);

        $wordList->lexical_entries()->detach($entryId);

        return response()->json([
            'message' => 'Entry removed from word list',
        ]);
    }

    /**
     * Removes several lexical entries from a word list in one request.
     */
    public function removeEntries(Request $request, int $wordListId): JsonResponse
    {
        $wordList = WordList::forAccount($request->user())
            ->findOrFail($wordListId);

        $data = $request->validate([
            'lexical_entry_ids' => 'required|array|max:1000',
            'lexical_entry_ids.*' => 'integer',
        ]);

        $numberOfEntries = $wordList->lexical_entries()->detach($data['lexical_entry_ids']);

        return response()->json([
            'number_of_entries' => $numberOfEntries,
        ]);
    }

    /**
     * Moves or copies several lexical entries from one word list to another.
     *
     * Both lists are resolved through the account scope, so a caller cannot move entries out of, or
     * into, a list they do not own.
     */
    public function moveEntries(Request $request, int $wordListId): JsonResponse
    {
        $account = $request->user();

        $data = $request->validate([
            'lexical_entry_ids' => 'required|array|max:1000',
            'lexical_entry_ids.*' => 'integer',
            'target_word_list_id' => 'required|integer|different:'.$wordListId,
            'copy' => 'sometimes|boolean',
        ]);

        $wordList = WordList::forAccount($account)->findOrFail($wordListId);
        $targetWordList = WordList::forAccount($account)->findOrFail($data['target_word_list_id']);

        // Only entries genuinely held by the source list may be moved: the request body is not
        // trusted as a source of identifiers in its own right.
        $lexicalEntryIds = $wordList->lexical_entries()
            ->whereIn('lexical_entries.id', $data['lexical_entry_ids'])
            ->pluck('lexical_entries.id')
            ->all();

        if (count($lexicalEntryIds) < 1) {
            return response()->json([
                'number_of_entries' => 0,
            ]);
        }

        DB::transaction(function () use ($wordList, $targetWordList, $lexicalEntryIds, $data) {
            // syncWithoutDetaching leaves entries the target already holds untouched, so moving a
            // duplicate is a no-op rather than an error.
            $targetWordList->lexical_entries()->syncWithoutDetaching($lexicalEntryIds);

            if (empty($data['copy'])) {
                $wordList->lexical_entries()->detach($lexicalEntryIds);
            }
        });

        return response()->json([
            'number_of_entries' => count($lexicalEntryIds),
        ]);
    }

    /**
     * Batch-check which of the given lexical entries appear in any of the
     * authenticated user's word lists.  Designed to be called once per
     * glossary page load — accepts up to 1 000 IDs and returns a lean
     * set of the ones that matched.
     */
    public function checkMembership(Request $request): JsonResponse
    {
        $data = $request->validate([
            'lexical_entry_ids' => 'required|array|max:1000',
            'lexical_entry_ids.*' => 'integer',
        ]);

        $userListIds = WordList::forAccount($request->user())
            ->pluck('id');

        // GROUP BY is cheaper than DISTINCT on MariaDB — it can use a
        // loose index scan on a covering index (word_list_id, lexical_entry_id).
        $savedIds = WordListEntry::whereIn('word_list_id', $userListIds)
            ->whereIn('lexical_entry_id', $data['lexical_entry_ids'])
            ->groupBy('lexical_entry_id')
            ->pluck('lexical_entry_id');

        return response()->json([
            'saved_lexical_entry_ids' => $savedIds,
        ]);
    }

    /**
     * Reorder entries in word list
     */
    public function reorderEntries(Request $request, int $wordListId): JsonResponse
    {
        $data = $request->validate([
            'entries' => 'required|array',
            'entries.*.lexical_entry_id' => 'required|integer',
            'entries.*.order' => 'required|integer',
        ]);

        // This may seem like throw-away, but we're intentionally doing this to ensure
        // that the user has permission to edit this word list.
        WordList::where('account_id', $request->user()->id)->findOrFail($wordListId);

        foreach ($data['entries'] as $entry) {
            WordListEntry::where('word_list_id', $wordListId)
                ->where('lexical_entry_id', $entry['lexical_entry_id'])
                ->update(['order' => $entry['order']]);
        }

        return response()->json([
            'message' => 'Word list reordered',
        ]);
    }
}
