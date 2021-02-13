<?php

namespace App\Aws;

use Aws\Credentials\CredentialProvider;
use Aws\Comprehend\ComprehendClient;

class ComprehendFactory
{
    public function create(): ComprehendClient
    {
        $provider = CredentialProvider::defaultProvider();
        return new ComprehendClient([
            'credentials' => $provider,
            'region'      => config('aws.comprehend.region'),
            'version'     => '2017-11-27'
        ]);
    }
}
