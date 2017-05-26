<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Sense extends Model
{
    protected $fillable = [ 'id', 'description' ];

    public function word() 
    {
        return $this->belongsTo(Word::class, 'id', 'id');
    }

    public function translations() 
    {
        return $this->hasMany(Translation::class);
    }

    public function keywords() 
    {
        return $this->hasMany(Keyword::class);
    }
}
