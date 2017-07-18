<?php

namespace App\Models;

class Sense extends ModelBase
{
    protected $fillable = [ 'id', 'description' ];
    public $incrementing = false;

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
