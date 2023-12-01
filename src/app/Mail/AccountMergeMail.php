<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AccountMergeMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var string
     */
    private $_requestId;

    /**
     * @var string
     */
    private $_providerList;

    /**
     * @var string
     */
    private $_token;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $requestId, string $providerList, string $token)
    {
        $this->_requestId = $requestId;
        $this->_providerList = $providerList;
        $this->_token = $token;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->subject(config('app.name').' - Confirm account linking');

        return $this->markdown('emails.account.merge', [
            'requestId' => $this->_requestId,
            'providerList' => $this->_providerList,
            'token' => $this->_token
        ]);
    }
}
