<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LexicalEntryDetail extends ModelBase
{
    use Traits\HasAccount;

    protected $table = 'lexical_entry_details';

    protected $fillable = [
        'lexical_entry_id', 'category', 'text', 'order', 'type',
    ];

    public function lexical_entry(): BelongsTo
    {
        return $this->belongsTo(LexicalEntry::class, 'lexical_entry_id');
    }
}
