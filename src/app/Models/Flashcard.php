<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Flashcard extends Model
{
    public function language() 
    {
        return $this->belongsTo(Language::class);
    }
}