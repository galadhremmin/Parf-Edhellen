<?php

namespace App\Models;

class SystemError extends ModelBase
{
    protected $fillable = ['message', 'url', 'error', 'account_id', 'ip', 'is_common', 'category', 'user_agent', 'session_id', 'file', 'line', 'duration'];
}
