<?php

namespace App\Interfaces;

interface IExternalToInternalUrlResolver
{
    function getInternalUrl(string $url): ?string;
    function isHostQualified(string $host): bool;
}
