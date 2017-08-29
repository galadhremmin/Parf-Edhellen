<?php

namespace App\Models;

class FlashcardResult extends ModelBase
{
    use Traits\HasAccountTrait;
    
    public function flashcard() 
    {
        return $this->belongsTo(Flashcard::class);
    }

    public function translation() 
    {
        return $this->belongsTo(Translation::class);
    }

    public function account() 
    {
        return $this->belongsTo(Account::class);
    }
}
