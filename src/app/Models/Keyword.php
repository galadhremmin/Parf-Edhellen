<?php

namespace App\Models;

class Keyword extends ModelBase
{
    protected $fillable = [
        'keyword', 
        'normalized_keyword', 
        'reversed_normalized_keyword', 
        'gloss_id', 
        'word_id', 
        'sense_id', 
        'is_sense', 
        'normalized_keyword_unaccented', 
        'reversed_normalized_keyword_unaccented',
        'normalized_keyword_length', 
        'reversed_normalized_keyword_length', 
        'normalized_keyword_unaccented_length',
        'reversed_normalized_keyword_unaccented_length',
        'word'
    ];

    /**
     * Retrieves the Word entity associated with this keyword. It is deliberately suffixed `Entity` because `word` exists as a column. :(
     */
    public function wordEntity() 
    {
        return $this->belongsTo(Word::class, 'word_id');
    }

    public function scopeFindByWord($query, string $word, $reversed = false, $includeOld = true) 
    {
        $filter = [
            [$reversed ? 'reversed_normalized_keyword_unaccented' : 'normalized_keyword_unaccented', 'like', $word]
        ];

        if (! $includeOld) {
            $filter[] = ['is_old', 0];
        }

        $query->where($filter)
            ->whereNotNull('sense_id');
    }
}
