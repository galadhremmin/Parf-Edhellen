<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Word extends Model
{
    protected $table = 'word';
    protected $primaryKey = 'KeyID';

    /**
     * Disable automatic timestamps.
     */
    public $timestamps = false;
}
