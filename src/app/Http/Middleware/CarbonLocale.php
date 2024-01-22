<?php

namespace App\Http\Middleware;

use Closure;
use Carbon\Carbon;

class CarbonLocale
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $locale = \Locale::parseLocale($request->server('HTTP_ACCEPT_LANGUAGE'));
        $languageName = $locale['language'] ?: 'en';

        Carbon::setLocale($languageName);
        Carbon::setToStringFormat('c');

        return $next($request);
    }

}
