<?php

namespace App\Factories;

use App\Interfaces\ISystemLanguageFactory;
use App\Models\Language;

class DefaultSystemLanguageFactory implements ISystemLanguageFactory
{
    private $_language = null;

    public function __construct()
    {
        $languageName = config('ed.system_language');
        $this->_language = Language::where('name', $languageName)->first();
    }

    function language(): ?Language
    {
        return $this->_language;
    }
}
