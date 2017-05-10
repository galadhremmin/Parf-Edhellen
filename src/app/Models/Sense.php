<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Sense extends Model
{
    public function word() 
    {
        return $this->belongsTo(Word::class, 'id', 'id');
    }

    public function keywords() 
    {
        return $this->hasMany(Keyword::class);
    }
}
