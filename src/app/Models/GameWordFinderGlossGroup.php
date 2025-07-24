<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\LexicalEntryGroup;

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
