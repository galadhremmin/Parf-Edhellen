<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Models\Contribution;

class ContributionApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    private $_cancellationToken;
    private $_contribution;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $cancellationToken, Contribution $post)
    {
        $this->_cancellationToken = $cancellationToken;
        $this->_contribution = $post;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->subject(config('app.name').' - Your contribution was approved');

        return $this->markdown('emails.contribution.approved', [
            'cancellationToken' => $this->_cancellationToken,
            'contribution' => $this->_contribution
        ]);
    }
}
