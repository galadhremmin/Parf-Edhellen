<?php

namespace App\Events;

use App\Models\ForumPost;
use Illuminate\Queue\SerializesModels;

class ForumPostDeleted
{
    use SerializesModels;

    public ForumPost $post;

    public int $accountId;

    public function __construct(ForumPost $post, int $accountId)
    {
        $this->post = $post;
        $this->accountId = $accountId;
    }
}
