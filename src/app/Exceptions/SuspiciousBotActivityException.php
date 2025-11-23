<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;

class SuspiciousBotActivityException extends Exception
{
    public function __construct(Request $request, string $component, ?array $assessmentResult = null)
    {
        parent::__construct('Suspicious bot activity from '.$request->ip().' affecting component '.$component.
            (is_array($assessmentResult) && count($assessmentResult) > 0 ? ' with assessment result: '.json_encode($assessmentResult, JSON_PRETTY_PRINT) : ''));
    }
}
