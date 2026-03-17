<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CrosswordPuzzle extends ModelBase
{
    protected $fillable = [
        'language_id', 'puzzle_date', 'grid', 'clues',
    ];

    protected $casts = [
        'puzzle_date' => 'date',
        'grid' => 'array',
        'clues' => 'array',
    ];

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    /**
     * @return HasMany<CrosswordCompletion, CrosswordPuzzle>
     */
    public function completions(): HasMany
    {
        return $this->hasMany(CrosswordCompletion::class, 'crossword_puzzle_id');
    }
}
