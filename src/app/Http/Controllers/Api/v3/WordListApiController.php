<?php

namespace App\Http\Controllers\Api\v3;

use App\Adapters\FlashcardDeckAdapter;
use App\Adapters\WordListAdapter;
use App\Events\FlashcardFlipped;
use App\Helpers\LinkHelper;
use App\Http\Controllers\Abstracts\Controller;
use App\Models\FlashcardResult;
use App\Models\LexicalEntry;
use App\Models\WordList;
use App\Models\WordListEntry;
use App\Services\Flashcards\FlashcardAnswerChecker;
use App\Services\Flashcards\FlashcardDeckBuilder;
use App\Services\Flashcards\FlashcardDirection;
use App\Services\Flashcards\WordListDeckSource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WordListApiController extends Controller
{
    private WordListAdapter $_adapter;

    private FlashcardDeckBuilder $_deckBuilder;

    private FlashcardDeckAdapter $_deckAdapter;

    private FlashcardAnswerChecker $_answerChecker;

    private LinkHelper $_link;

    public function __construct(
        WordListAdapter $adapter,
        FlashcardDeckBuilder $deckBuilder,
        FlashcardDeckAdapter $deckAdapter,
        FlashcardAnswerChecker $answerChecker,
        LinkHelper $linkHelper
    ) {
        $this->_adapter = $adapter;
        $this->_deckBuilder = $deckBuilder;
        $this->_deckAdapter = $deckAdapter;
        $this->_answerChecker = $answerChecker;
        $this->_link = $linkHelper;
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

    /**
     * Deals a finite deck of flashcards from the words in this list.
     *
     * POST rather than GET: the body carries the retry subset, and the response is not
     * deterministic, so it must never be cached.
     */
    public function deck(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'direction' => 'sometimes|string|in:forward,reverse',
            'limit' => 'sometimes|integer|min:1|max:'.FlashcardDeckBuilder::MAXIMUM_SIZE,
            'lexical_entry_ids' => 'sometimes|array|max:'.FlashcardDeckBuilder::MAXIMUM_SIZE,
            'lexical_entry_ids.*' => 'integer',
        ]);

        $wordList = $this->findStudyableOrFail($request, $id);

        $deck = $this->_deckBuilder->build(
            new WordListDeckSource($wordList),
            FlashcardDirection::parse($data['direction'] ?? null),
            $data['limit'] ?? FlashcardDeckBuilder::DEFAULT_SIZE,
            // WordListDeckSource intersects this with the list's own membership, so it can never be
            // used to read entries the list does not hold.
            $data['lexical_entry_ids'] ?? null
        );

        return response()->json([
            'deck' => $this->_deckAdapter->adapt($deck, $wordList->id),
        ]);
    }

    /**
     * Scores a finished deck and records the results.
     *
     * Correctness is re-derived here rather than taken from the request: the answer travels to the
     * client inside the card, so a tampered client could otherwise claim anything.
     */
    public function deckResults(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'direction' => 'sometimes|string|in:forward,reverse',
            'answers' => 'required|array|max:'.FlashcardDeckBuilder::MAXIMUM_SIZE,
            'answers.*.lexical_entry_id' => 'required|integer',
            'answers.*.gloss_id' => 'nullable|integer',
            // nullable, not merely present: the ConvertEmptyStringsToNull middleware rewrites the
            // empty answer of an abandoned card to null before validation ever sees it.
            'answers.*.answer' => 'present|nullable|string',
        ]);

        $account = $request->user();
        $wordList = $this->findStudyableOrFail($request, $id);
        $direction = FlashcardDirection::parse($data['direction'] ?? null);

        $lexicalEntryIds = array_map(fn ($answer) => (int) $answer['lexical_entry_id'], $data['answers']);

        // One query for every entry in the deck, and only entries the list actually holds.
        $entries = $wordList->lexical_entries()
            ->whereIn('lexical_entries.id', $lexicalEntryIds)
            ->with(['word', 'glosses'])
            ->get()
            ->keyBy('id');

        $cards = [];
        $results = [];
        $numberOfCorrect = 0;

        foreach ($data['answers'] as $answer) {
            $entry = $entries->get((int) $answer['lexical_entry_id']);
            if ($entry === null) {
                continue;
            }

            $offered = (string) ($answer['answer'] ?? '');
            $expected = $this->expectedAnswer($entry, $direction, $answer['gloss_id'] ?? null);
            $acceptable = $direction === FlashcardDirection::Forward
                ? $entry->glosses->pluck('translation')->filter()->values()->all()
                : [(string) ($entry->word?->word ?? '')];

            $correct = $this->_answerChecker->isCorrect(
                $offered, $acceptable, fn () => $this->synonyms($entry, $direction)
            );

            if ($correct) {
                $numberOfCorrect += 1;
            }

            $cards[] = [
                'lexical_entry_id' => $entry->id,
                'correct' => $correct,
                'expected' => $expected,
                'actual' => $offered,
                // Supplied so the client can render its summary without joining back against the
                // deck it already discarded.
                'word' => (string) ($entry->word?->word ?? ''),
                'url' => $this->_link->lexicalEntry($entry->id),
            ];

            $results[] = [
                'entry' => $entry,
                'expected' => $expected,
                'actual' => $offered,
                'correct' => $correct,
            ];
        }

        $this->recordResults($account, $wordList, $direction, $results);

        return response()->json([
            'results' => [
                'number_of_correct' => $numberOfCorrect,
                'number_of_wrong' => count($cards) - $numberOfCorrect,
                'cards' => $cards,
            ],
        ]);
    }

    /**
     * Persists a scored deck.
     *
     * Rows are saved one at a time with their event rather than mass inserted, because the
     * milestone achievements in AuditTrailSubscriber listen for FlashcardFlipped and would
     * otherwise never fire.
     */
    private function recordResults($account, WordList $wordList, FlashcardDirection $direction, array $results): void
    {
        if ($account === null || empty($results)) {
            return;
        }

        // Hoisted out of the loop: the per-card endpoint runs this count once per card, which is
        // twenty full table counts for a twenty card deck.
        $numberOfCards = FlashcardResult::where('account_id', $account->id)->count();

        DB::transaction(function () use ($account, $wordList, $direction, $results, &$numberOfCards) {
            foreach ($results as $result) {
                $flashcardResult = new FlashcardResult;
                $flashcardResult->flashcard_id = null;
                $flashcardResult->word_list_id = $wordList->id;
                $flashcardResult->account_id = $account->id;
                $flashcardResult->lexical_entry_id = $result['entry']->id;
                $flashcardResult->expected = mb_substr($result['expected'], 0, 255);
                $flashcardResult->actual = mb_substr($result['actual'], 0, 255);
                $flashcardResult->correct = $result['correct'];
                $flashcardResult->direction = $direction->value;
                $flashcardResult->save();

                $numberOfCards += 1;
                event(new FlashcardFlipped($flashcardResult, $numberOfCards));
            }
        });
    }

    /**
     * The answer shown on the back of the card.
     */
    private function expectedAnswer($entry, FlashcardDirection $direction, ?int $glossId): string
    {
        if ($direction === FlashcardDirection::Reverse) {
            return (string) ($entry->word?->word ?? '');
        }

        $gloss = $glossId !== null
            ? $entry->glosses->firstWhere('id', $glossId)
            : null;

        return (string) ($gloss?->translation ?? $entry->glosses->first()?->translation ?? '');
    }

    /**
     * Other words carrying the same sense, in the same language.
     *
     * Language scoped on purpose: `elen` and `êl` are cognates across Quenya and Sindarin, and
     * accepting one for the other would defeat the exercise.
     *
     * @return string[]
     */
    private function synonyms($entry, FlashcardDirection $direction): array
    {
        if ($entry->sense_id === null) {
            return [];
        }

        $related = LexicalEntry::query()
            ->where('sense_id', $entry->sense_id)
            ->where('language_id', $entry->language_id)
            ->where('id', '!=', $entry->id)
            ->where('is_deleted', 0)
            ->where('is_rejected', 0)
            ->with(['word', 'glosses'])
            ->get();

        if ($direction === FlashcardDirection::Reverse) {
            return $related->pluck('word.word')->filter()->values()->all();
        }

        return $related->flatMap(fn ($e) => $e->glosses->pluck('translation'))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Resolves a word list that the requester is allowed to study — their own, or anybody's public
     * list. 404 rather than 403 for a private list: its very existence is private.
     */
    private function findStudyableOrFail(Request $request, int $id): WordList
    {
        $account = $request->user();
        $wordList = WordList::findOrFail($id);

        if (! $wordList->is_public && $wordList->account_id !== $account?->id) {
            abort(404);
        }

        return $wordList;
    }
}
