<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Gloss extends ModelBase
{
    protected $table = 'glosses';

    protected $fillable = [
        'lexical_entry_id', 'gloss',
    ];

    protected $hidden = [
        'lexical_entry_id',
        'created_at',
        'updated_at',
    ];

    public function lexical_entry(): BelongsTo
    {
        return $this->belongsTo(LexicalEntry::class, 'lexical_entry_id');
    }
}
