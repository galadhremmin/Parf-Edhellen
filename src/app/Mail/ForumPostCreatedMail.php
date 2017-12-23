<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Models\ForumPost;

class ForumPostCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    private $_post;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(ForumPost $post)
    {
        $this->_post = $post;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->subject(config('app.name').' - New posts notification');

        return $this->markdown('emails.forum.post-created', [
            'post' => $this->_post
        ]);
    }
}
