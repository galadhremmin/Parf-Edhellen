<?php

namespace App\Adapters;

use App\Helpers\LinkHelper;
use App\Interfaces\IMarkdownParser;
use App\Models\Language;
use App\Services\Flashcards\FlashcardCandidate;
use App\Services\Flashcards\FlashcardDeck;
use App\Services\Flashcards\FlashcardDeckCard;
use App\Services\Flashcards\FlashcardDirection;
use App\Services\Flashcards\FlashcardSkippedCard;

/**
 * Shapes a dealt deck for the client.
 *
 */
class FlashcardDeckAdapter
{
    private IMarkdownParser $_markdownParser;

    private LinkHelper $_link;

    /** @var array<int,string|null>|null */
    private ?array $_tengwarModes = null;

    public function __construct(IMarkdownParser $markdownParser, LinkHelper $linkHelper)
    {
        $this->_markdownParser = $markdownParser;
        $this->_link = $linkHelper;
    }

    public function adapt(FlashcardDeck $deck, int $wordListId): array
    {
        return [
            'word_list_id' => $wordListId,
            'word_list_name' => $deck->name,
            'direction' => $deck->direction->value,
            'option_count' => $deck->optionCount,
            'number_of_requested' => $deck->numberOfRequested,
            'cards' => array_map(
                fn (FlashcardDeckCard $card) => $this->adaptCard($card, $deck->direction),
                $deck->cards
            ),
            'skipped' => array_map(fn (FlashcardSkippedCard $skipped) => [
                'lexical_entry_id' => $skipped->lexicalEntryId,
                'word' => $skipped->word,
                'reason' => $skipped->reason,
            ], $deck->skipped),
        ];
    }

    private function adaptCard(FlashcardDeckCard $card, FlashcardDirection $direction): array
    {
        $entry = $card->entry;
        $word = (string) ($entry->word?->word ?? '');

        $answer = $direction === FlashcardDirection::Forward ? $card->translation : $word;

        // Keys are positional and assigned after shuffling, so two options carrying the same text
        // are still addressable one from the other — the client uses them as React keys and to
        // score the flip.
        $options = [];
        $options[] = ['text' => $answer, 'tengwar' => $direction === FlashcardDirection::Reverse
            ? $this->tengwarFor($word, $entry->tengwar, $entry->language_id)
            : null];

        foreach ($card->distractors as $distractor) {
            $options[] = [
                'text' => $distractor->textFor($direction),
                'tengwar' => $direction === FlashcardDirection::Reverse
                    ? $this->tengwarForCandidate($distractor)
                    : null,
            ];
        }

        shuffle($options);

        $correctOptionKey = null;
        foreach ($options as $index => $option) {
            $options[$index]['key'] = 'o'.$index;

            // Compared by identity of position rather than by text: the answer may legitimately
            // appear only once, and the first positional match after the shuffle is it.
            if ($correctOptionKey === null && $option['text'] === $answer) {
                $correctOptionKey = 'o'.$index;
            }
        }

        return [
            'card_id' => 'c'.$entry->id.'-'.($card->glossId ?? 0),
            'lexical_entry_id' => $entry->id,
            'gloss_id' => $card->glossId,
            'prompt' => $direction === FlashcardDirection::Forward ? $word : $card->translation,
            'prompt_tengwar' => $direction === FlashcardDirection::Forward
                ? $this->tengwarFor($word, $entry->tengwar, $entry->language_id)
                : null,
            'options' => $options,
            'back' => [
                'answer' => $answer,
                'correct_option_key' => $correctOptionKey,
                'word' => $word,
                'translations' => $entry->glosses->pluck('translation')->filter()->values()->all(),
                'comments' => $entry->comments
                    ? $this->_markdownParser->parseMarkdown($entry->comments)
                    : null,
                'source' => $entry->source,
                'url' => $this->_link->lexicalEntry($entry->id),
            ],
        ];
    }

    private function tengwarForCandidate(FlashcardCandidate $candidate): ?array
    {
        return $this->tengwarFor($candidate->word, $candidate->tengwar, $candidate->languageId);
    }

    /**
     * Tengwar is decided per word, never per page: a word list may mix languages, and each card
     * must be transcribed in its own language's mode.
     */
    private function tengwarFor(string $word, ?string $override, ?int $languageId): ?array
    {
        if ($word === '' || $languageId === null) {
            return null;
        }

        $mode = $this->tengwarModes()[$languageId] ?? null;
        if (empty($mode)) {
            return null;
        }

        // lexical_entries.tengwar is already transcribed, so the client must render it as-is rather
        // than putting it through Glaemscribe a second time.
        return empty($override)
            ? ['text' => $word, 'mode' => $mode, 'transcribe' => true]
            : ['text' => $override, 'mode' => $mode, 'transcribe' => false];
    }

    /**
     * @return array<int,string|null>
     */
    private function tengwarModes(): array
    {
        if ($this->_tengwarModes === null) {
            $this->_tengwarModes = Language::query()
                ->pluck('tengwar_mode', 'id')
                ->all();
        }

        return $this->_tengwarModes;
    }
}
