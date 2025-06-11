<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameWordFinderLanguage extends ModelBase implements Interfaces\IHasFriendlyName, Interfaces\IHasLanguage
{
    protected $fillable = [
        'language_id', 'title', 'description',
    ];

    protected $primaryKey = 'language_id';

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function getFriendlyName()
    {
        return $this->title;
    }
}
