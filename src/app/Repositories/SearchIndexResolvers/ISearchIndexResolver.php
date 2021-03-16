<?php

namespace App\Repositories\SearchIndexResolvers;

use App\Repositories\ValueObjects\SearchIndexSearchValue;

interface ISearchIndexResolver
{
    function resolve(SearchIndexSearchValue $value): array;
}
