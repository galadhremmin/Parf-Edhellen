<?php

namespace App\Models\Versioning;

use App\Models\ModelBase;
use App\Models\Traits\HasAccount;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GlossDetailVersion extends ModelBase
{
    use HasAccount;

    protected $fillable = [
        'gloss_version_id', 'category', 'text', 'order', 'type',
    ];

    public function gloss(): BelongsTo
    {
        return $this->belongsTo(GlossVersion::class);
    }
}
