<?php

namespace App\Models;

class FlashcardResult extends ModelBase
{
    use Traits\HasAccount;
    
    public function flashcard() 
    {
        return $this->belongsTo(Flashcard::class);
    }

    public function translation() 
    {
        return $this->belongsTo(Translation::class);
    }
}
