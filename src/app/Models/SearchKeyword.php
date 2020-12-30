<?php

namespace App\Models;

class SearchKeyword extends ModelBase
{
    protected $fillable = [
        'keyword',
        'normalized_keyword',
        'normalized_keyword_reversed',
        'normalized_keyword_unaccented',
        'normalized_keyword_reversed_unaccented',
        'keyword_length',
        'normalized_keyword_length',
        'normalized_keyword_reversed_length',
        'normalized_keyword_unaccented_length',
        'normalized_keyword_reversed_unaccented_length',
        'entity_name',
        'entity_id',
        'language_id',
        'speech_id',
        'gloss_group_id',
        'is_old',
        'word',
        'word_id'
    ];
}
