<?php

namespace App\Models;

class FlashcardResult extends ModelBase
{
    use Traits\HasAccount;

    protected $fillable = ['flashcard_id', 'account_id', 'gloss_id', 'expected', 'actual', 'correct'];
    
    public function flashcard() 
    {
        return $this->belongsTo(Flashcard::class);
    }

    public function gloss() 
    {
        return $this->belongsTo(Gloss::class);
    }
}
