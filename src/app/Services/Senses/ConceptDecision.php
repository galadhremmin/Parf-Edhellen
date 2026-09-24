<?php

namespace App\Services\Senses;

use App\Services\Enumerations\ConceptRelation;

/**
 * What a judge answered for one sense: the WordNet meanings it has, or a word to hang a new concept on.
 */
class ConceptDecision
{
    /**
     * @param  string[]  $synsetIds  the meanings it has, the main one first
     */
    public function __construct(
        public readonly int $senseId,
        public readonly array $synsetIds,
        public readonly ?string $betterWord,
        public readonly ?ConceptRelation $relation,
        public readonly int $confidence,
    ) {}

    /**
     * Reads one decision as the answer schema writes it.
     *
     * @param  array<string, mixed>  $answer
     */
    public static function fromAnswer(array $answer): self
    {
        $word = trim((string) ($answer['better_word'] ?? ''));

        return new self(
            (int) $answer['sense_id'],
            array_values(array_filter(array_map('strval', $answer['synset_ids'] ?? []))),
            $word === '' ? null : $word,
            ConceptRelation::tryFrom((string) ($answer['relation'] ?? '')),
            (int) ($answer['confidence'] ?? 0),
        );
    }
}
