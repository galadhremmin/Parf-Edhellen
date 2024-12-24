<?php

namespace App\Aws;

use Aws\Credentials\AssumeRoleCredentialProvider;
use Aws\Credentials\CredentialProvider;
use Aws\Sts\StsClient;

class CredentialProviderFactory
{
    public static function create()
    {
        $iniPath = config('aws.config_path');
        if (empty($iniPath)) {
            $provider = CredentialProvider::defaultProvider();
        } else {
            $defaultProvider = CredentialProvider::ini(null, $iniPath);

            $assumeRoleCredentials = new AssumeRoleCredentialProvider([
                'client' => new StsClient([
                    'credentials' => $defaultProvider,
                    'region' => config('aws.region'),
                    'version' => 'latest',
                ]),
                'assume_role_params' => [
                    'RoleArn' => 'arn:aws:iam::007151906553:role/ElfdictProdRole',
                    'RoleSessionName' => 'ED'.ucfirst(config('app.env')),
                ],
            ]);

            // To avoid unnecessarily fetching STS credentials on every API operation,
            // the memoize function handles automatically refreshing the credentials when they expire
            $provider = CredentialProvider::memoize($assumeRoleCredentials);
        }

        return $provider;
    }
}
