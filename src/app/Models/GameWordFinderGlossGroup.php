<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameWordFinderGlossGroup extends ModelBase
{
    protected $fillable = [
        'gloss_group_id',
    ];

    public function gloss_group(): BelongsTo
    {
        return $this->belongsTo(GlossGroup::class);
    }
}
