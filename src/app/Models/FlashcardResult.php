<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlashcardResult extends ModelBase
{
    use Traits\HasAccount;

    protected $fillable = ['flashcard_id', 'word_list_id', 'account_id', 'lexical_entry_id', 'expected', 'actual', 'correct', 'direction'];

    public function flashcard(): BelongsTo
    {
        return $this->belongsTo(Flashcard::class);
    }

    public function lexical_entry(): BelongsTo
    {
        return $this->belongsTo(LexicalEntry::class, 'lexical_entry_id');
    }

    public function word_list(): BelongsTo
    {
        return $this->belongsTo(WordList::class, 'word_list_id');
    }
}
