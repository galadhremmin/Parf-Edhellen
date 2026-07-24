<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LexicalEntryPhoneticDevelopment extends ModelBase
{
    protected $table = 'lexical_entry_phonetic_developments';

    protected $fillable = [
        'derivation_group_uuid', 'lexical_entry_id', 'order', 'word', 'rule',
        'previous_word', 'language_id',
    ];

    protected $hidden = ['created_at', 'updated_at'];

    public function lexical_entry(): BelongsTo
    {
        return $this->belongsTo(LexicalEntry::class, 'lexical_entry_id');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
