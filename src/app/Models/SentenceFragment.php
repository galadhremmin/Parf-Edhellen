<?php

namespace App\Models;

class SentenceFragment extends ModelBase
{
    protected $fillable = [ 
        'fragment', 'tengwar', 'comments', 'speech_id', 'gloss_id', 'sentence_id',
        'order', 'is_linebreak', 'type'
    ];
    
    public function sentence() 
    {
        return $this->belongsTo(Sentence::class);
    }

    public function speech()
    {
        return $this->belongsTo(Speech::class);
    }

    public function inflection_associations()
    {
        return $this->hasMany(SentenceFragmentInflectionRel::class);
    }
}
