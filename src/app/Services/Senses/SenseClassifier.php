<?php

namespace App\Services\Senses;

use App\Interfaces\IWordNetLexicon;
use App\Models\Gloss;
use App\Models\LexicalEntry;
use App\Models\Sense;
use App\Models\Speech;
use App\Services\Enumerations\SenseKind;
use App\Services\Enumerations\WordNetPos;
use DateInterval;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Tells senses that mean something (a concept can capture them) from names, grammatical labels and function words,
 * and which WordNet parts of speech a sense's entries allow. Works from the entries' parts of speech, which are rows
 * an administrator can add, so the vocabulary it reads them by lives in `config/ed-senses.php`.
 */
class SenseClassifier
{
    public function __construct(private readonly IWordNetLexicon $_lexicon) {}

    /**
     * A sense is a name, a grammatical label or a function word when its entries' parts of speech all say so. Failing
     * that, a capitalised sense whose headword is no English word is a name, and one that only repeats the word it
     * glosses translates nothing; otherwise it is lexical, unless its headword is one of English's function words.
     *
     * @param  Sense  $sense  with `lexical_entries` and `terms` loaded
     */
    public function classify(Sense $sense): SenseKind
    {
        $kinds = $this->speechesOf($sense)->map(fn (string $name) => $this->kindOf($name))->filter();

        // a part of speech that names the kind outright settles it: Thingol is a name whatever its gloss says
        if ($kinds->isNotEmpty() && ! $kinds->contains(SenseKind::LEXICAL)) {
            return $kinds->countBy(fn (SenseKind $kind) => $kind->value)
                ->sortDesc()
                ->keys()
                ->map(fn (string $kind) => SenseKind::from($kind))
                ->first();
        }

        if ($this->looksLikeAName($sense)) {
            return SenseKind::NAME;
        }

        if ($this->isUntranslated($sense)) {
            return SenseKind::UNTRANSLATED;
        }

        if ($kinds->contains(SenseKind::LEXICAL)) {
            return SenseKind::LEXICAL;
        }

        $headword = $sense->terms->first()?->lemma;

        return $headword !== null && in_array($headword, $this->vocabulary('function_words'), true)
            ? SenseKind::FUNCTION_WORD
            : SenseKind::LEXICAL;
    }

    /**
     * The WordNet parts of speech the sense's entries allow.
     *
     * @param  Sense  $sense  with `lexical_entries` loaded
     * @return WordNetPos[]|null null when the entries don't say
     */
    public function wordNetPos(Sense $sense): ?array
    {
        $bySpeech = config('ed-senses.wordnet_pos');
        $allowed = $this->speechesOf($sense)
            ->flatMap(fn (string $name) => $bySpeech[$name] ?? [])
            ->map(fn (string $pos) => WordNetPos::from($pos));

        return $allowed->isEmpty() ? null : $allowed->unique()->values()->all();
    }

    /**
     * Whether a sense that records no part of speech is nonetheless a name: it is capitalised, and its headword is a
     * word no dictionary of English has. Gondor, Mithlond and Estë are names; Oxford and May are left alone, because
     * WordNet knows them and an editor should decide.
     *
     * @param  Sense  $sense  with `word` and `terms` loaded
     */
    private function looksLikeAName(Sense $sense): bool
    {
        $headword = $sense->terms->first()?->lemma;

        // a capital letter to open, in data that is otherwise lower case
        return $headword !== null
            && preg_match('/^\p{Lu}/u', trim($sense->word->word)) === 1
            && ! $this->_lexicon->isLemma($headword);
    }

    /**
     * Whether the sense only repeats the word it glosses, in every entry and every gloss. Such a sense translates
     * nothing, so no meaning can be read from it: "#manda" glossed "#manda". A gloss that says more ("mallorn",
     * glossed "golden tree") makes it an ordinary sense.
     *
     * @param  Sense  $sense  with `word` and `lexical_entries.word`, `lexical_entries.glosses` loaded
     */
    private function isUntranslated(Sense $sense): bool
    {
        $text = $this->compare($sense->word->word);

        return $sense->lexical_entries->isNotEmpty() && $sense->lexical_entries->every(
            fn (LexicalEntry $entry) => $this->compare($entry->word->word) === $text
                && $entry->glosses->every(fn (Gloss $gloss) => $this->compare($gloss->translation) === $text)
        );
    }

    private function compare(?string $value): string
    {
        return mb_strtolower(trim((string) $value));
    }

    /**
     * The parts of speech of the entries that use the sense, skipping entries that record none.
     *
     * @param  Sense  $sense  with `lexical_entries` loaded
     * @return Collection<int, string> by name
     */
    public function speechesOf(Sense $sense): Collection
    {
        $names = $this->speechNamesById();

        return $sense->lexical_entries
            ->map(fn (LexicalEntry $entry) => $names[$entry->speech_id] ?? null)
            ->filter();
    }

    /**
     * The kind of sense an entry's part of speech implies, or null when it implies nothing.
     */
    private function kindOf(string $speech): ?SenseKind
    {
        return match (true) {
            in_array($speech, $this->vocabulary('name_speeches'), true) => SenseKind::NAME,
            in_array($speech, $this->vocabulary('grammar_speeches'), true) => SenseKind::GRAMMAR,
            in_array($speech, $this->vocabulary('function_speeches'), true) => SenseKind::FUNCTION_WORD,
            in_array($speech, $this->vocabulary('unknown_speeches'), true) => null,
            default => SenseKind::LEXICAL,
        };
    }

    /**
     * One of the vocabularies in `config/ed-senses.php`.
     *
     * @return string[]
     */
    private function vocabulary(string $key): array
    {
        return config('ed-senses.'.$key, []);
    }

    /**
     * Every part of speech's name by ID, cached: parts of speech change about once a year.
     *
     * @return array<int, string>
     */
    private function speechNamesById(): array
    {
        return Cache::remember('ed.speech.names', DateInterval::createFromDateString('1 day'),
            fn () => Speech::pluck('name', 'id')->all());
    }
}
