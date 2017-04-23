<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SentenceFragmentInflectionRel extends Model
{
    public function sentenceFragment()
    {
        return $this->belongsTo(SentenceFragment::class);
    }

    public function inflection()
    {
        return $this->hasOne(Inflection::class);
    }
}
