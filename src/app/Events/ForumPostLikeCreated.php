<?php

namespace App\Events;

use App\Models\ForumPost;
use Illuminate\Queue\SerializesModels;

class ForumPostLikeCreated
{
    use SerializesModels;

    public function __construct(readonly ForumPost $post, readonly int $accountId)
    {}
}
