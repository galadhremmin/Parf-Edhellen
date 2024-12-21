<?php

namespace App\Aws;

use Aws\Comprehend\ComprehendClient;

class ComprehendFactory
{
    public function create(): ComprehendClient
    {
        $provider = CredentialProviderFactory::create();

        return new ComprehendClient([
            'credentials' => $provider,
            'region' => config('aws.region'),
            'version' => '2017-11-27',
        ]);
    }
}
