<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Translation extends ModelBase
{
    protected $fillable = [
        'gloss_id', 'translation',
    ];

    protected $hidden = [
        'gloss_id',
        'created_at',
        'updated_at',
    ];

    public function gloss(): BelongsTo
    {
        return $this->belongsTo(Gloss::class);
    }
}
