<?php

namespace App\Mail;

use App\Models\ForumPost;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ForumPostOnProfileMail extends Mailable
{
    use Queueable, SerializesModels;

    private string $_cancellationToken;

    private ForumPost $_post;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $cancellationToken, ForumPost $post)
    {
        $this->_cancellationToken = $cancellationToken;
        $this->_post = $post;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->subject(config('app.name').' - New posts on your profile');

        return $this->markdown('emails.forum.post-profile', [
            'cancellationToken' => $this->_cancellationToken,
            'post' => $this->_post,
        ]);
    }
}
