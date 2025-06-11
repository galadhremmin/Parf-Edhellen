<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GlossDetail extends ModelBase
{
    use Traits\HasAccount;

    protected $fillable = [
        'gloss_id', 'category', 'text', 'order', 'type',
    ];

    public function gloss(): BelongsTo
    {
        return $this->belongsTo(Gloss::class);
    }
}
