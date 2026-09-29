<?php

namespace App\Http\Middleware;

use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken as BaseValidateCsrfToken;

class CustomValidateCsrfToken extends BaseValidateCsrfToken
{
    public function __construct(Application $app, Encrypter $encrypter)
    {
        parent::__construct($app, $encrypter);

        // Read-only lookups that act on nobody's behalf, so a stale token needn't fail them.
        // List exact paths: anything that writes or signs in must stay protected.
        $api = 'api/v'.config('ed.api_version');
        $this->except = [
            $api.'/book/find',
            $api.'/book/entities/*',
            $api.'/lexical-entry/suggest',
            $api.'/account/find',
            $api.'/utility/markdown',
        ];
    }
}
