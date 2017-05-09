<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Speech extends Model
{
    public function sentence_fragments()
    {
        return $this->hasMany(SentenceFragment::class);
    }
}