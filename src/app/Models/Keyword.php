<?php

namespace App\Models;

class Keyword extends ModelBase
{
    protected $fillable = ['keyword', 'normalized_keyword', 'reversed_normalized_keyword', 'translation_id', 
        'word_id', 'sense_id', 'is_sense', 'normalized_keyword_unaccented', 'reversed_normalized_keyword_unaccented'];

    public function scopeFindByWord($query, string $word, $reversed = false) 
    {
        $query->distinct()
            ->where($reversed ? 'reversed_normalized_keyword_unaccented' : 'normalized_keyword_unaccented', 'like', $word)
            ->whereNotNull('sense_id');
    }
}
