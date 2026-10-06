<?php

namespace App\Services\Senses;

/**
 * One entry glossed with a sense, as the review page shows it: how the word is used, and where to read it.
 */
class SenseReviewUsage implements \JsonSerializable
{
    public function __construct(
        public readonly int $lexicalEntryId,
        public readonly string $word,
        public readonly string $language,
        public readonly ?string $speech,
        // the glosses that add to the sense, the ones that only restate it having been dropped
        public readonly string $glosses,
        public readonly string $url,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'lexical_entry_id' => $this->lexicalEntryId,
            'word' => $this->word,
            'language' => $this->language,
            'speech' => $this->speech,
            'glosses' => $this->glosses,
            'url' => $this->url,
        ];
    }
}
