<?php

namespace App\Interfaces;

interface IExternalToInternalUrlResolver
{
    public function getSources(): array;

    public function getInternalUrl(string $url): ?string;

    public function isHostQualified(string $host): bool;
}
