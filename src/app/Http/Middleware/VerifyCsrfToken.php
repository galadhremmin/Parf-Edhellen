<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;
use Closure;


class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'api/v2/book/languages',
        'api/v2/book/translate/*',
        'api/v2/book/translate',
        'api/v2/book/suggest',
        'api/v2/book/find',
        'api/v2/speech/*',
        'api/v2/inflection/*'
    ];
}
