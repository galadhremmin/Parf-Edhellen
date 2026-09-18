<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SenseTerm extends ModelBase
{
    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['sense_id', 'position', 'term', 'term_key', 'is_verb'];

    protected $casts = [
        'is_verb' => 'boolean',
    ];

    public function sense(): BelongsTo
    {
        return $this->belongsTo(Sense::class);
    }
}
