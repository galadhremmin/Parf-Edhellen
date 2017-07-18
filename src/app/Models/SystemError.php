<?php

namespace App\Models;

class SystemError extends ModelBase
{
    protected $fillable = [ 'message', 'url', 'error', 'account_id' ];
}
