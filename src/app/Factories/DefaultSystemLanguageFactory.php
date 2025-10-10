<?php

namespace App\Factories;

use App\Interfaces\ISystemLanguageFactory;
use App\Models\Language;

class DefaultSystemLanguageFactory implements ISystemLanguageFactory
{
    private ?Language $_language = null;

    public function __construct()
    {
        $languageName = config('ed.system_language');
        try {
            $this->_language = Language::where('name', $languageName)->first();
        } catch (\Exception $e) {
            $this->_language = null;
        }
    }

    public function language(): ?Language
    {
        return $this->_language;
    }
}
