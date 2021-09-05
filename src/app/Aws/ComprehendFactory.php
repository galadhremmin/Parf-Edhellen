<?php

namespace App\Aws;

use Aws\Credentials\{
    AssumeRoleCredentialProvider,
    CredentialProvider
};
use Aws\Comprehend\ComprehendClient;
use Aws\Sts\StsClient;

class ComprehendFactory
{
    public function create(): ComprehendClient
    {
        $provider = CredentialProviderFactory::create();
        return new ComprehendClient([
            'credentials' => $provider,
            'region'      => config('aws.region'),
            'version'     => '2017-11-27'
        ]);
    }
}
