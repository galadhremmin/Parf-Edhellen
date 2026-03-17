<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Traits\HasAccount;

class CrosswordCompletion extends ModelBase
{
    use HasAccount;

    protected $fillable = [
        'account_id', 'crossword_puzzle_id', 'seconds_elapsed', 'is_assisted',
    ];

    protected $casts = [
        'is_assisted' => 'boolean',
    ];

    public function crosswordPuzzle(): BelongsTo
    {
        return $this->belongsTo(CrosswordPuzzle::class, 'crossword_puzzle_id');
    }
}
