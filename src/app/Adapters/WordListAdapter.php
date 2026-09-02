<?php

namespace App\Adapters;

use App\Helpers\LinkHelper;
use App\Helpers\StringHelper;
use App\Models\LexicalEntry;
use App\Models\WordList;
use Illuminate\Support\Collection;

/**
 * Shapes word lists and their entries for the API.
 *
 * The word list endpoints previously returned Eloquent models directly, which leaked internal
 * columns (account_id among them) and gave the frontend no stable contract.
 */
class WordListAdapter
{
    private LinkHelper $_link;

    public function __construct(LinkHelper $linkHelper)
    {
        $this->_link = $linkHelper;
    }

    /**
     * Shapes a word list without its entries, for the index view.
     *
     * `lexical_entries_count` is only present when the caller asked for it via withCount().
     */
    public function adaptSummary(WordList $wordList, ?int $accountId = null): array
    {
        return [
            'id' => $wordList->id,
            'name' => $wordList->name,
            'description' => $wordList->description,
            'is_public' => (bool) $wordList->is_public,
            'is_mine' => $accountId !== null && $wordList->account_id === $accountId,
            'number_of_entries' => $wordList->lexical_entries_count !== null
                ? (int) $wordList->lexical_entries_count
                : null,
            'contains_entry' => $wordList->contains_entry !== null
                ? ((int) $wordList->contains_entry) > 0
                : null,
            'url' => route('word-list.show', [
                'id' => $wordList->id,
                'name' => $this->toSeoName($wordList->name),
            ]),
            'study_url' => route('word-list.study', ['id' => $wordList->id]),
            'created_at' => $wordList->created_at,
            'updated_at' => $wordList->updated_at,
        ];
    }

    /**
     * Shapes a word list together with its entries.
     */
    public function adapt(WordList $wordList, ?int $accountId = null): array
    {
        $wordList->loadMissing('account');

        $summary = $this->adaptSummary($wordList, $accountId);

        $entries = $wordList->lexical_entries->map(
            fn (LexicalEntry $lexicalEntry) => $this->adaptEntry($lexicalEntry)
        )->all();

        return array_merge($summary, [
            'number_of_entries' => count($entries),
            'account' => $wordList->account ? [
                'id' => $wordList->account->id,
                'nickname' => $wordList->account->nickname,
                'url' => $this->_link->author($wordList->account->id, $wordList->account->nickname),
            ] : null,
            'entries' => $entries,
        ]);
    }

    /**
     * Shapes a word list for somebody browsing the owner's profile: enough to decide whether the
     * list is worth opening, and nothing that only its owner has any business seeing.
     *
     * `preview_words` is populated from whatever entries happen to be loaded, so the caller decides
     * how many words to fetch. It is a taste of the list, not its contents.
     */
    public function adaptPreview(WordList $wordList): array
    {
        return [
            'id' => $wordList->id,
            'name' => $wordList->name,
            'description' => $wordList->description,
            'number_of_entries' => $wordList->lexical_entries_count !== null
                ? (int) $wordList->lexical_entries_count
                : null,
            'preview_words' => $wordList->relationLoaded('lexical_entries')
                ? $wordList->lexical_entries
                    ->map(fn (LexicalEntry $lexicalEntry) => $lexicalEntry->word?->word)
                    ->filter()
                    ->values()
                    ->all()
                : [],
            'url' => route('word-list.show', [
                'id' => $wordList->id,
                'name' => $this->toSeoName($wordList->name),
            ]),
        ];
    }

    /**
     * @param  Collection<int,WordList>  $wordLists
     */
    public function adaptMany(Collection $wordLists, ?int $accountId = null): array
    {
        return $wordLists->map(
            fn (WordList $wordList) => $this->adaptSummary($wordList, $accountId)
        )->all();
    }

    /**
     * Shapes a single entry as a row in the word list view.
     *
     * The translation is a first-class field rather than something the client has to dig out of a
     * nested collection: it is displayed in its own column beside every word.
     */
    private function adaptEntry(LexicalEntry $lexicalEntry): array
    {
        $separator = config('ed.gloss_translations_separator');

        return [
            'lexical_entry_id' => $lexicalEntry->id,
            'word' => $lexicalEntry->word?->word,
            'normalized_word' => $lexicalEntry->word?->normalized_word,
            'tengwar' => $lexicalEntry->tengwar,
            'translation' => $lexicalEntry->glosses->implode('translation', $separator),
            'type' => $lexicalEntry->speech?->name,
            'language' => $lexicalEntry->language ? [
                'id' => $lexicalEntry->language->id,
                'name' => $lexicalEntry->language->name,
                'short_name' => $lexicalEntry->language->short_name,
                'tengwar_mode' => $lexicalEntry->language->tengwar_mode,
            ] : null,
            'url' => $this->_link->lexicalEntry($lexicalEntry->id),
            'order' => $lexicalEntry->pivot?->order,
            'added_at' => $lexicalEntry->pivot?->created_at,
        ];
    }

    /**
     * Reduces a list name to the slug used in its canonical URL.
     */
    public function toSeoName(?string $name): ?string
    {
        $seoName = StringHelper::normalizeForUrl($name ?? '');

        return $seoName === '' ? null : $seoName;
    }
}
