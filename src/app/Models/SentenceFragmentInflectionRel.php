<?php

namespace App\Models;

class SentenceFragmentInflectionRel extends ModelBase
{
    protected $fillable = [
        'sentence_fragment_id', 'inflection_id'
    ];

    public function sentence_fragment()
    {
        return $this->belongsTo(SentenceFragment::class);
    }

    public function inflection()
    {
        return $this->hasOne(Inflection::class);
    }
}
