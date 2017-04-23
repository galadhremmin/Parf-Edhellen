<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SentenceFragment extends Model
{
    public function sentence() 
    {
        return $this->belongsTo(Sentence::class);
    }

    public function speech()
    {
        return $this->hasOne(Speech::class);
    }

    public function inflectionAssociations()
    {
        return $this->hasMany(SentenceFragmentInflectionAssoc::class);
    }

    public function isPunctuationOrWhitespace() 
    {
        return preg_match('/^[,\\.!\\?\\s]$/', $this->Fragment);
    }
}
