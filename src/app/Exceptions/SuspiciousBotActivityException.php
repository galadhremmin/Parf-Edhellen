<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;

class SuspiciousBotActivityException extends Exception
{
    public function __construct(Request $request, string $component)
    {
        parent::__construct('Suspicious bot activity from '.$request->ip().' affecting component '.$component);
    }
}
