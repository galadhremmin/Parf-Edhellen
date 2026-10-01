<?php

namespace App\Mail;

use App\Security\SignInChallenge;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SignInCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        private string $_code,
        private string $_nickname,
        private string $_providerName,
    ) {}

    public function build()
    {
        $this->subject(config('app.name').' - Your sign-in code');

        return $this->markdown('emails.account.sign-in-code', [
            'code' => $this->_code,
            'nickname' => $this->_nickname,
            'providerName' => $this->_providerName,
            'lifetime' => SignInChallenge::LIFETIME_MINUTES,
        ]);
    }
}
