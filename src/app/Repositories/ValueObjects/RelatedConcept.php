<?php

namespace App\Repositories\ValueObjects;

/**
 * A concept a search can move to: a kind of what was asked for, or what it is itself a kind of.
 */
class RelatedConcept implements \JsonSerializable
{
    public function __construct(
        public readonly string $label,
        public readonly int $entries,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return ['label' => $this->label, 'entries' => $this->entries];
    }
}
