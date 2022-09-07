<?php

namespace App\Models;

class GlossInflection extends ModelBase
{
    protected $fillable = [
        'inflection_group_uuid', 'gloss_id', 'inflection_id', 'account_id', 'sentence_id',
        'sentence_fragment_id', 'order', 'language_id', 'speech_id', 'is_neologism', 
        'is_rejected', 'source', 'word', 'sentence_fragment_id'
    ];
    protected $hidden = ['created_at', 'updated_at'];

    public function gloss()
    {
        return $this->belongsTo(Gloss::class);
    }

    public function sentence()
    {
        return $this->belongsTo(Sentence::class);
    }

    public function sentence_fragment()
    {
        return $this->belongsTo(SentenceFragment::class);
    }

    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    public function speech()
    {
        return $this->belongsTo(Speech::class);
    }

    public function inflection()
    {
        return $this->belongsTo(Inflection::class);
    }
}
