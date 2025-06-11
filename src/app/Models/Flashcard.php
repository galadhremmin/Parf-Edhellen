<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Flashcard extends ModelBase implements Interfaces\IHasLanguage
{
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
