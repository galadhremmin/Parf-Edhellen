<?php

namespace App\Events;

use App\Models\ForumPost;
use Illuminate\Queue\SerializesModels;

class ForumPostCreated
{
    use SerializesModels;

    public $post;
    public $accountId;
    
    public function __construct(ForumPost $post, int $accountId)
    {
        $this->post = $post;
        $this->accountId = $accountId;
    }
}
