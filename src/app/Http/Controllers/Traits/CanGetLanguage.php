<?php
namespace App\Http\Controllers\Traits;

use App\Models\Language;

trait CanGetLanguage
{
    public function getLanguageByShortName(?string $shortName = null)
    {
        if ($shortName === null || empty($shortName)) {
            return null;
        }

        $language = Language::shortName($shortName)->first();
        return $language;
    }
}
