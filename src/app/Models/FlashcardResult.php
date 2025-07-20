<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlashcardResult extends ModelBase
{
    use Traits\HasAccount;

    protected $fillable = ['flashcard_id', 'account_id', 'gloss_id', 'expected', 'actual', 'correct'];

    public function flashcard(): BelongsTo
    {
        return $this->belongsTo(Flashcard::class);
    }

    public function gloss(): BelongsTo
    {
        return $this->belongsTo(Gloss::class);
    }
}
