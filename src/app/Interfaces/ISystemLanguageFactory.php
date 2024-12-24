<?php

namespace App\Interfaces;

use App\Models\Language;

interface ISystemLanguageFactory
{
    public function language(): ?Language;
}
