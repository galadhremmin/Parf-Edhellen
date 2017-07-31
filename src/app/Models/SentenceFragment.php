<?php

namespace App\Models;

class SentenceFragment extends ModelBase
{
    protected $fillable = [ 'translation_id' ];
    
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
