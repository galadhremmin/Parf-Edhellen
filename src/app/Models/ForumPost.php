<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ForumPost extends Model
{
    protected $fillable = [ 'context', 'parent_form_post_id', 'account_id', 'content' ];
}
