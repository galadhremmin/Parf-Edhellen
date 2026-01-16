<?php

namespace App\Exceptions;

use Exception;

class WebAuthnException extends Exception
{
    /**
     * Create a new WebAuthn exception instance.
     *
     * @param string $message
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct(string $message = '', int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Render the exception into an HTTP response.
     */
    public function render()
    {
        return response()->json([
            'error' => $this->message,
        ], 400);
    }
}
