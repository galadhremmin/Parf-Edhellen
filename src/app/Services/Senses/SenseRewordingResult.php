<?php

namespace App\Services\Senses;

/**
 * What came of rewording a sense: the entries moved, or why none were.
 */
class SenseRewordingResult implements \JsonSerializable
{
    private function __construct(
        public readonly int $entries,
        public readonly ?string $sense,
        public readonly ?int $senseId,
        public readonly ?string $refusal,
    ) {}

    public static function moved(int $entries, string $sense, int $senseId): self
    {
        return new self($entries, $sense, $senseId, null);
    }

    public static function refused(string $reason): self
    {
        return new self(0, null, null, $reason);
    }

    public function wasMoved(): bool
    {
        return $this->refusal === null;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'entries' => $this->entries,
            'sense' => $this->sense,
            'sense_id' => $this->senseId,
            'refusal' => $this->refusal,
        ];
    }
}
