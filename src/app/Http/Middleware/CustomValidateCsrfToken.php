<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken as BaseValidateCsrfToken;

class CustomValidateCsrfToken extends BaseValidateCsrfToken
{
    /**
     * The names of the attributes that should not be trimmed.
     *
     * @var array
     */
    protected $except = [
        'api/*', // Exclude API routes from CSRF validation
    ];
}
