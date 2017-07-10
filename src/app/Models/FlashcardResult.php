<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class FlashcardResult extends Model
{
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
