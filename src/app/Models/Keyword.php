<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Keyword extends ModelBase
{
    protected $fillable = [
        'keyword',
        'normalized_keyword',
        'lexical_entry_id',
        'word_id',
        'sense_id',
        'is_sense',
        'word',
    ];

    /**
     * Retrieves the Word entity associated with this keyword. It is deliberately suffixed `Entity` because `word` exists as a column. :(
     * @return BelongsTo<Word>
     */
    public function wordEntity(): BelongsTo
    {
        return $this->belongsTo(Word::class, 'word_id');
    }

    /**
     * @return BelongsTo<Sense>
     */
    public function sense(): BelongsTo
    {
        return $this->belongsTo(Sense::class, 'sense_id');
    }

    /**
     * @return BelongsTo<Language>
     */
    public function keyword_language(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'keyword_language_id', 'id');
    }
}
