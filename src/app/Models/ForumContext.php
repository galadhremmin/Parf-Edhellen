<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ForumContext extends Model
{
    const CONTEXT_FORUM       = 1;
    const CONTEXT_TRANSLATION = 2;
    const CONTEXT_SENTENCE    = 3;
    const CONTEXT_ACCOUNT     = 4;

    protected $fillable = [ 'name' ];
}
