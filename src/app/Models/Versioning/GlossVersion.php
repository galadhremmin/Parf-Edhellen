<?php

namespace App\Models\Versioning;

use App\Models\ModelBase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GlossVersion extends ModelBase
{
    protected $table = 'gloss_versions';

    protected $fillable = [
        'lexical_entry_version_id', 'translation',
    ];

    protected $hidden = [
        'lexical_entry_version_id',
        'created_at',
        'updated_at',
    ];

    public function lexical_entry_version(): BelongsTo
    {
        return $this->belongsTo(LexicalEntryVersion::class, 'lexical_entry_version_id');
    }
}
