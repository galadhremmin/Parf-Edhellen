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
        $languageName = ($locale === null || ! is_array($locale)) ? 'en' : $locale['language'];

        Carbon::setLocale($languageName);
        Carbon::setToStringFormat('c');

        return $next($request);
    }

}
