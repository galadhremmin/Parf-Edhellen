<?php

namespace App\Repositories\ValueObjects;

/**
 * A wording the dictionary already glosses words with, offered so that a new entry can join it rather than start a
 * near-identical sense of its own.
 */
class SenseSuggestion implements \JsonSerializable
{
    public function __construct(
        public readonly int $senseId,
        public readonly string $sense,
        public readonly int $entries,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'senseId' => $this->senseId,
            'sense' => $this->sense,
            'entries' => $this->entries,
        ];
    }
}
