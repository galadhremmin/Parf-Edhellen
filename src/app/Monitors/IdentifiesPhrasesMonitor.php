<?php

namespace App\Monitors;

use App\Interfaces\IHealthMonitor;
use App\Interfaces\IIdentifiesPhrases;

class IdentifiesPhrasesMonitor implements IHealthMonitor
{
    private IIdentifiesPhrases $_provider;

    public function __construct(IIdentifiesPhrases $provider)
    {
        $this->_provider = $provider;
    }

    /**
     * Performs a self-check function to ensure that the integration works
     * as intended. TODO: move this somewher else.
     */
    public function testOnce(): ?\Exception
    {
        try {
            $this->_provider->detectKeyPhrases(
                'Earendil was a mariner
                that tarried in Arvernien;
                he built a boat of timber felled
                in Nimbrethil to journey in;
                her sails he wove of silver fair,
                of silver were her lanterns made,
                her prow was fashioned like a swan
                and light upon her banners laid.'
            );
        } catch (\Exception $ex) {
            return $ex;
        }

        return null;
    }
}
