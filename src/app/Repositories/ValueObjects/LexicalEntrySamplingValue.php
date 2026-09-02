<?php

namespace App\Repositories\ValueObjects;

/**
 * Constraints for drawing a random sample of lexical entries.
 *
 * Deliberately expressed as plain data-access criteria: it carries no notion of what the sample is
 * *for*. Games narrow their own rules down to these fields before handing them to the repository,
 * which keeps game vocabulary out of an otherwise ubiquitous class.
 */
class LexicalEntrySamplingValue implements \JsonSerializable
{
    use Traits\CanInitialize;

    public function __construct($properties)
    {
        $this->initializeAll($properties, [
            'language_ids',
            'lexical_entry_group_ids',
            'speech_ids',
            'exclude_speech_ids',
            'exclude_lexical_entry_ids',
            'exclude_translations',
            'limit',
        ], false /* = not required */);
    }

    /**
     * Restricts the sample to these languages. Empty means every language.
     *
     * @return int[]
     */
    public function getLanguageIds(): array
    {
        return $this->getValue('language_ids') ?? [];
    }

    /**
     * Restricts the sample to these lexical entry groups. Empty means every group.
     *
     * @return int[]
     */
    public function getLexicalEntryGroupIds(): array
    {
        return $this->getValue('lexical_entry_group_ids') ?? [];
    }

    /**
     * Restricts the sample to these parts of speech. Empty means every part of speech.
     *
     * @return int[]
     */
    public function getSpeechIds(): array
    {
        return $this->getValue('speech_ids') ?? [];
    }

    /**
     * Excludes these parts of speech from the sample. Empty means nothing is excluded.
     *
     * @return int[]
     */
    public function getExcludeSpeechIds(): array
    {
        return $this->getValue('exclude_speech_ids') ?? [];
    }

    /**
     * Entries that must never appear in the sample.
     *
     * @return int[]
     */
    public function getExcludeLexicalEntryIds(): array
    {
        return $this->getValue('exclude_lexical_entry_ids') ?? [];
    }

    /**
     * Gloss translations that must never appear in the sample, such as placeholders.
     *
     * @return string[]
     */
    public function getExcludeTranslations(): array
    {
        return $this->getValue('exclude_translations') ?? [];
    }

    public function getLimit(): int
    {
        return (int) ($this->getValue('limit') ?? 100);
    }
}
