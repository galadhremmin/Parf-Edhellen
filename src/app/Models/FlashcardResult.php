<?php

namespace App\Models;

class FlashcardResult extends ModelBase
{
    use Traits\HasAccount;
    
    public function flashcard() 
    {
        return $this->belongsTo(Flashcard::class);
    }

    public function gloss() 
    {
        return $this->belongsTo(Gloss::class);
    }
}
