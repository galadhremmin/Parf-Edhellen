<?php

namespace App\Models;

class ForumDiscussion extends ModelBase
{
    use Traits\HasAccount;
    protected $fillable = [  'account_id' ];
}
