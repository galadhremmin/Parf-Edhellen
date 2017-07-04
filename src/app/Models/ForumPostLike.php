<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ForumPostLike extends Model
{
    protected $fillable = [ 'forum_post_id', 'account_id' ];
}
