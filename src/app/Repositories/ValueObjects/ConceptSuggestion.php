<?php

namespace App\Repositories\ValueObjects;

/**
 * A meaning offered to someone writing a sense: what it is called, what it means, and what it is a kind of.
 */
class ConceptSuggestion implements \JsonSerializable
{
    /**
     * @param  string[]  $synonyms  the other words it goes by, which is how a search for "cottage" finds "bungalow"
     * @param  string[]  $lineage  what it is a kind of, nearest first
     */
    public function __construct(
        public readonly int $id,
        public readonly string $label,
        public readonly string $definition,
        public readonly array $synonyms,
        public readonly array $lineage,
        public readonly int $entries,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'definition' => $this->definition,
            'synonyms' => $this->synonyms,
            'lineage' => $this->lineage,
            'entries' => $this->entries,
        ];
    }
}
