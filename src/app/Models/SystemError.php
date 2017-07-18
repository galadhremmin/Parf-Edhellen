<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class SystemError extends Model
{
    protected $fillable = [ 'message', 'url', 'error', 'account_id' ];
}
