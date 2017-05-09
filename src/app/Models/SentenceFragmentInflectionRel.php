<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SentenceFragmentInflectionRel extends Model
{
    public function sentence_fragment()
    {
        return $this->belongsTo(SentenceFragment::class);
    }

    public function inflection()
    {
        return $this->hasOne(Inflection::class);
    }
}
