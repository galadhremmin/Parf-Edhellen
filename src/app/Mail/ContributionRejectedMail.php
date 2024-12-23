<?php

namespace App\Mail;

use App\Models\Contribution;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContributionRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    private string $_cancellationToken;

    private Contribution $_contribution;

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
        $this->subject(config('app.name').' - Your contribution was rejected');

        return $this->markdown('emails.contribution.rejected', [
            'cancellationToken' => $this->_cancellationToken,
            'contribution' => $this->_contribution,
        ]);
    }
}
