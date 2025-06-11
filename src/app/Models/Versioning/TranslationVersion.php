<?php

namespace App\Models\Versioning;

use App\Models\ModelBase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TranslationVersion extends ModelBase
{
    protected $fillable = [
        'gloss_version_id', 'translation',
    ];

    protected $hidden = [
        'gloss_version_id',
        'created_at',
        'updated_at',
    ];

    public function gloss(): BelongsTo
    {
        return $this->belongsTo(GlossVersion::class);
    }
}
