<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SearchKeyword extends ModelBase implements Interfaces\IHasLanguage
{
    public const SEARCH_GROUP_UNASSIGNED = 0;

    public const SEARCH_GROUP_DICTIONARY = 1;

    public const SEARCH_GROUP_SENTENCE = 2;

    public const SEARCH_GROUP_FORUM_POST = 3;

    protected $fillable = [
        'search_group',
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
        'lexical_entry_group_id',
        'is_old',
        'word',
        'word_id',
        'keyword_language_id',
        'is_keyword_language_invented',
    ];

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function keyword_language(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'keyword_language_id', 'id');
    }

    public function entity(): MorphTo
    {
        return $this->morphTo('entity', 'entity_name', 'entity_id');
    }
}
