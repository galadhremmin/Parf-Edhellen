<?php

namespace App\Models;

class SentenceFragment extends ModelBase
{
    protected $fillable = [ 'type', 'fragment', 'tengwar', 'comments', 'speech_id', 'translation_id',
        'order', 'sentence_id' ];
    
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
