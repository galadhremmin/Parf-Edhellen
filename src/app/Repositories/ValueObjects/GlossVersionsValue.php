<?php

namespace App\Repositories\ValueObjects;

use Illuminate\Support\Collection;

class GlossVersionsValue implements \JsonSerializable
{
    use Traits\CanInitialize;

    public function __construct($properties)
    {
        $this->initializeAll($properties, [
            'versions',
            'latest_version_id',
        ]);
    }

    /**
     * Get a collection of versions
     *
     * @return Collection
     */
    public function getVersions()
    {
        return $this->getValue('versions');
    }

    /**
     * Get the ID for the latest version
     *
     * @return int
     */
    public function getLatestVersionId()
    {
        return $this->getValue('latest_version_id');
    }
}
