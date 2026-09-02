<?php

namespace App\Services\Flashcards;

use App\Interfaces\IFlashcardDeckSource;
use App\Models\WordList;
use Illuminate\Support\Collection;

/**
 * Deals a deck from the words somebody saved to one of their word lists.
 */
class WordListDeckSource implements IFlashcardDeckSource
{
    private WordList $_wordList;

    /** @var Collection<int,\App\Models\LexicalEntry>|null */
    private ?Collection $_entries = null;

    public function __construct(WordList $wordList)
    {
        $this->_wordList = $wordList;
    }

    public function getName(): string
    {
        return $this->_wordList->name;
    }

    public function getLanguageIds(): array
    {
        return $this->entries()
            ->pluck('language_id')
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function getLexicalEntryGroupIds(): array
    {
        // Left open on purpose. A word list may hold words from several sources, and narrowing
        // distractors to the same groups would empty the pool for the smaller ones.
        return [];
    }

    public function getCandidateEntries(?array $lexicalEntryIds, int $limit): Collection
    {
        $entries = $this->entries();

        if ($lexicalEntryIds !== null) {
            // Intersected against actual membership rather than trusted: otherwise the request body
            // would be an oracle for reading any entry in the dictionary through a list the caller
            // happens to own.
            $wanted = array_flip(array_map('intval', $lexicalEntryIds));
            $entries = $entries->filter(fn ($entry) => isset($wanted[$entry->id]));
        }

        return $entries->shuffle()->take($limit)->values();
    }

    /**
     * @return Collection<int,\App\Models\LexicalEntry>
     */
    private function entries(): Collection
    {
        if ($this->_entries === null) {
            $this->_entries = $this->_wordList->lexical_entries()
                ->where('lexical_entries.is_deleted', 0)
                ->where('lexical_entries.is_rejected', 0)
                ->with(['word', 'language', 'speech', 'glosses'])
                ->get();
        }

        return $this->_entries;
    }
}
