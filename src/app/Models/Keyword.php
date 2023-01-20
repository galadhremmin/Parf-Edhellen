<?php

namespace App\Models;

class Keyword extends ModelBase
{
    protected $fillable = [
        'keyword',
        'normalized_keyword',
        'gloss_id',
        'word_id',
        'sense_id',
        'is_sense',
        'word'
    ];

    /**
     * Retrieves the Word entity associated with this keyword. It is deliberately suffixed `Entity` because `word` exists as a column. :(
     */
    public function wordEntity() 
    {
        return $this->belongsTo(Word::class, 'word_id');
    }

    public function sense()
    {
        return $this->belongsTo(Sense::class, 'sense_id');
    }

    public function keyword_language()
    {
        return $this->belongsTo(Language::class, 'keyword_language_id', 'id');
    }
}
