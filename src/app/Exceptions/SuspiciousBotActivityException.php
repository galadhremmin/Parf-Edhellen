<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;

class SuspiciousBotActivityException extends Exception
{
    /**
     * The assessment result from the Recaptcha API. This is an optional parameter that may be null.
     */
    private ?array $_assessmentResult = null;

    public function __construct(Request $request, string $component, ?array $_assessmentResult = null)
    {
        $this->_assessmentResult = $_assessmentResult;
        parent::__construct('Suspicious bot activity from '.$request->ip().' affecting component '.$component);
    }

    /**
     * Returns the assessment result from the Recaptcha API.
     *
     * @return ?array
     */
    public function getAssessmentResult(): ?array
    {
        return $this->_assessmentResult;
    }
}
