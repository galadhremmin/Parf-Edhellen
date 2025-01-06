<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;

class CarbonLocale
{
    const string DEFAULT_CARBON_LOCALE = 'en';

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $locale = \Locale::parseLocale($request->server('HTTP_ACCEPT_LANGUAGE'));
        $languageName = ($locale === null || ! is_array($locale)) ? self::DEFAULT_CARBON_LOCALE : $locale['language'];

        try {
            Carbon::setLocale($languageName);
        } catch (\Exception $ex) {
            Carbon::setLocale(self::DEFAULT_CARBON_LOCALE);
        }

        Carbon::macro('toStringFormat', function (Carbon $date) {
            return $date->format('c');
        });
        
        return $next($request);
    }
}
