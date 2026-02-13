<?php

namespace App\Http\Controllers\Api\v3;

use App\Http\Controllers\Abstracts\Controller;
use App\Models\WordList;
use App\Models\WordListEntry;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WordListApiController extends Controller
{
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
            'word_lists' => $query->get()
        ]);
    }

    /**
     * Get a specific word list with its entries
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $wordList = WordList::where(function($query) use ($request) {
                $query->where('account_id', $request->user()->id)
                      ->orWhere('is_public', true);
            })
            ->with(['lexical_entries' => function($query) {
                $query->with(['word', 'language', 'speech'])
                      ->orderBy('word_list_entries.order')
                      ->orderBy('word_list_entries.created_at');
            }])
            ->findOrFail($id);

        return response()->json([
            'word_list' => $wordList
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
            'is_public' => 'boolean'
        ]);

        $wordList = WordList::create([
            'account_id' => $request->user()->id,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_public' => $data['is_public'] ?? false
        ]);

        return response()->json([
            'word_list' => $wordList
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
            'is_public' => 'sometimes|boolean'
        ]);

        $wordList->update($data);

        return response()->json([
            'word_list' => $wordList
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
            'order' => 'nullable|integer'
        ]);

        // Check if entry already exists in word list
        if ($wordList->lexical_entries()->where('lexical_entry_id', $data['lexical_entry_id'])->exists()) {
            return response()->json([
                'error' => 'Entry already exists in word list'
            ], 400);
        }

        $wordList->lexical_entries()->attach($data['lexical_entry_id'], [
            'order' => $data['order'] ?? null
        ]);

        return response()->json([
            'message' => 'Entry added to word list'
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
            'message' => 'Entry removed from word list'
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
            'lexical_entry_ids'   => 'required|array|max:1000',
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
            'entries.*.order' => 'required|integer'
        ]);

        foreach ($data['entries'] as $entry) {
            WordListEntry::where('word_list_id', $wordListId)
                ->where('lexical_entry_id', $entry['lexical_entry_id'])
                ->update(['order' => $entry['order']]);
        }

        return response()->json([
            'message' => 'Word list reordered'
        ]);
    }
}
