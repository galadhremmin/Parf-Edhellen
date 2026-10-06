<?php

namespace App\Mail;

use App\Security\InboxCode;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerifyEmailAddressMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        private string $_code,
        private string $_link,
        private string $_nickname,
    ) {}

    public function build()
    {
        $this->subject(config('app.name').' - Confirm your e-mail address');

        return $this->markdown('emails.account.verify-email', [
            'code' => $this->_code,
            'link' => $this->_link,
            'nickname' => $this->_nickname,
            'lifetime' => InboxCode::LIFETIME_MINUTES,
        ]);
    }
}
