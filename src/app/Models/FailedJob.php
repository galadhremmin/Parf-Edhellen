<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FailedJob extends ModelBase
{
    protected $casts = [
        Model::CREATED_AT => 'datetime',
        Model::UPDATED_AT => 'datetime',
        'failed_at'       => 'datetime'
    ];
}
