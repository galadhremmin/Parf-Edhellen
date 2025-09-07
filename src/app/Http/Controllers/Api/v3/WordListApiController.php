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
     * Get all word lists for the authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        $wordLists = WordList::forAccount($request->user())
            ->withCount('lexical_entries')
            ->orderBy('name')
            ->get();

        return response()->json([
            'word_lists' => $wordLists
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
