<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ForumPost extends Model
{
    protected $fillable = [ 'topic', 'account_id', 'content' ];
}
