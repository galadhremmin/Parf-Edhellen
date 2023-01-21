<?php

namespace App\Interfaces;

use App\Models\Language;

interface ISystemLanguageFactory
{
    function language(): Language;
}
