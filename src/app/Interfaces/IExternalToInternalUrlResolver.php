<?php

namespace App\Interfaces;

interface IExternalToInternalUrlResolver
{
    function getSources(): array;
    function getInternalUrl(string $url): ?string;
    function isHostQualified(string $host): bool;
}
