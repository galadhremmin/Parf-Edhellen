<?php

namespace App\Repositories\SearchIndexResolvers;

use App\Repositories\ValueObjects\SearchIndexSearchValue;

interface ISearchIndexResolver
{
    public function resolve(SearchIndexSearchValue $value): array;

    public function resolveId(int $entityId): array;
}
