<?php

namespace App\Models;

class GlossInflection extends ModelBase
{
    protected $fillable = [
        'inflection_group_uuid', 'gloss_id', 'inflection_id', 'account_id',
        'sentence_fragment_id', 'order', 'language_id', 'speech_id', 'is_neologism', 
        'is_rejected', 'source'
    ];
    protected $hidden = ['created_at', 'updated_at'];

    public function gloss()
    {
        return $this->belongsTo(Gloss::class);
    }

    public function inflection()
    {
        return $this->belongsTo(Inflection::class);
    }
}
