<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameWordFinderGlossGroup extends ModelBase
{
    protected $fillable = [
        'lexical_entry_group_id',
    ];

    public function lexical_entry_group(): BelongsTo
    {
        return $this->belongsTo(LexicalEntryGroup::class, 'lexical_entry_group_id');
    }
}
