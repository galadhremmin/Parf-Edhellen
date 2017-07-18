<?php

namespace App\Models;

class Flashcard extends ModelBase
{
    public function language() 
    {
        return $this->belongsTo(Language::class);
    }
}
