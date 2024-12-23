<?php

namespace App\Http\Discuss;

/*
 * Allows Discuss thread to be mapped to an entity other than the one specified
 * by the request. This can be used to resolve the author's account from a gloss
 * entity.
 */
interface IDiscussEntityRemapper
{
    public function getRemappedEntityType(string $entityType): string;
}
