<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

abstract class ModelBase extends Model
{
    protected $dates = [
        'created_at',
        'updated_at'
        // 'deleted_at' <-- presently not supported
    ];
}
