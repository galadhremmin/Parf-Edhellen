<?php

namespace App\Helpers;

class CookieHelper
{
    private const EU_CONSENT_USE_CASES_COOKIE_NAME = 'ed-euconsent-usecases';

    public static function hasUserConsent(string $useCase)
    {
        if (! isset($_COOKIE[self::EU_CONSENT_USE_CASES_COOKIE_NAME])) {
            return true;
        }

        $useCases = explode('|', $_COOKIE[self::EU_CONSENT_USE_CASES_COOKIE_NAME]);
        return in_array($useCase, $useCases);
    }
}
