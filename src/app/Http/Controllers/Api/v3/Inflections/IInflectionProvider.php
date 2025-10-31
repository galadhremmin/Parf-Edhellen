<?php

namespace App\Http\Controllers\Api\v3\Inflections;

use App\Models\LexicalEntry;

interface IInflectionProvider
{
    /**
     * Returns specific inflected forms of words from Ungwe API for a given lexical entry.
     *
     * @param LexicalEntry $lexicalEntry
     * @return null|array{
     *   description: string,
     *   words: array<int, array{
     *     qwid: string,
     *     lemma: string,
     *     homonym: int,
     *     category: string,
     *     forms: array<int, array{
     *       tag: string,
     *       forms: array<int, string>
     *     }>
     *   }>,
     *   links: array<string, string>,
     *   runid: int
     *   url: string
     *   tengwarMode: string|null
     * }
     */
    public function getInflections(LexicalEntry $lexicalEntry): ?array;
}
