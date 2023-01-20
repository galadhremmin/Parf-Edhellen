<?php

namespace App\Models;

class Flashcard extends ModelBase implements Interfaces\IHasLanguage
{
    public function language() 
    {
        return $this->belongsTo(Language::class);
    }
}
