<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameCrosswordLanguage extends ModelBase implements Interfaces\IHasFriendlyName, Interfaces\IHasLanguage
{
    protected $fillable = [
        'language_id', 'title', 'description',
    ];

    protected $primaryKey = 'language_id';

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    /**
     * @return HasMany<CrosswordPuzzle, GameCrosswordLanguage>
     */
    public function puzzles(): HasMany
    {
        return $this->hasMany(CrosswordPuzzle::class, 'language_id', 'language_id');
    }

    public function getFriendlyName()
    {
        return $this->title ?? $this->language?->name ?? (string) $this->language_id;
    }
}
