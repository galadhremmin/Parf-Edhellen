<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameCrosswordRephraseSpeech extends ModelBase
{
    protected $fillable = [
        'speech_id',
    ];

    public function speech(): BelongsTo
    {
        return $this->belongsTo(Speech::class, 'speech_id');
    }
}
