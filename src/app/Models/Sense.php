<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Sense extends Model
{
    public function word() 
    {
        return $this->hasOne(Word::class, 'id', 'id');
    }
}
