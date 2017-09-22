<?php

namespace App\Models;

class ForumContext extends ModelBase
{
    const CONTEXT_FORUM        = 1;
    const CONTEXT_TRANSLATION  = 2;
    const CONTEXT_SENTENCE     = 3;
    const CONTEXT_ACCOUNT      = 4;
    const CONTEXT_CONTRIBUTION = 5;

    protected $fillable = [ 'name' ];
}
