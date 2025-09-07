<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WordListEntry extends ModelBase
{
    protected $table = 'word_list_entries';
    
    protected $fillable = [
        'word_list_id', 'lexical_entry_id', 'order'
    ];

    public function word_list(): BelongsTo
    {
        return $this->belongsTo(WordList::class);
    }

    public function lexical_entry(): BelongsTo
    {
        return $this->belongsTo(LexicalEntry::class);
    }
}
