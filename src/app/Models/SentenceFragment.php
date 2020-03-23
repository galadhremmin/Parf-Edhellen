<?php

namespace App\Models;

class SentenceFragment extends ModelBase
{
    protected $fillable = [ 
        'fragment', 'tengwar', 'comments', 'speech_id', 'gloss_id', 'sentence_id',
        'order', 'is_linebreak', 'type', 'paragraph_number', 'sentence_number'
    ];

    public function gloss()
    {
        return $this->belongsTo(Gloss::class);
    }
    
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

    public function keywords()
    {
        return $this->hasMany(Keyword::class);
    }
}
