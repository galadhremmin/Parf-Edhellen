<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LexicalEntryDerivation extends ModelBase
{
    protected $table = 'lexical_entry_derivations';

    protected $fillable = [
        'derivation_group_uuid', 'lexical_entry_id', 'order', 'parent_lexical_entry_id',
        'parent_external_id', 'parent_form', 'parent_language_id', 'is_uncertain', 'is_rejected',
        'source', 'comment', 'intermediate_stages',
    ];

    protected $hidden = ['created_at', 'updated_at'];

    protected $casts = [
        'intermediate_stages' => 'array',
    ];

    public function lexical_entry(): BelongsTo
    {
        return $this->belongsTo(LexicalEntry::class, 'lexical_entry_id');
    }

    public function parent_lexical_entry(): BelongsTo
    {
        return $this->belongsTo(LexicalEntry::class, 'parent_lexical_entry_id');
    }

    public function parent_language(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'parent_language_id');
    }
}
