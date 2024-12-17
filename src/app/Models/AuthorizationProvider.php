<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class AuthorizationProvider extends ModelBase
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'name_identifier',
        'logo_file_name'
    ];
}
