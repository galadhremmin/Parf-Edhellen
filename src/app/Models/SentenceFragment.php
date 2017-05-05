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
        return $this->belongsTo(Speech::class);
    }

    public function inflectionAssociations()
    {
        return $this->hasMany(SentenceFragmentInflectionRel::class);
    }

    public function isPunctuationOrWhitespace() 
    {
        return $this->is_linebreak || preg_match('/^[,\\.!\\?\\n\\s]$/', $this->fragment);
    }
}