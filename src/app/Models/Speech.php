<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Speech extends Model
{
    public function sentenceFragments()
    {
        return $this->hasMany(SentenceFragment::class);
    }
}