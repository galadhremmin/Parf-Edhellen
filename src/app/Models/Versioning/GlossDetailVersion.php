<?php

namespace App\Models\Versioning;

use App\Models\ModelBase;
use App\Models\Traits\HasAccount;

class GlossDetailVersion extends ModelBase
{
    use HasAccount;

    protected $fillable = [
        'gloss_version_id', 'category', 'text', 'order', 'type',
    ];

    public function gloss()
    {
        return $this->belongsTo(GlossVersion::class);
    }
}
