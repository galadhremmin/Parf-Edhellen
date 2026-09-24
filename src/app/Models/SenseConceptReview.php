<?php

namespace App\Models;

use App\Repositories\Enumerations\ConceptReviewReason;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SenseConceptReview extends ModelBase
{
    protected $primaryKey = 'sense_id';

    public $incrementing = false;

    protected $fillable = ['sense_id', 'reason', 'detail', 'confidence'];

    protected $casts = [
        'reason' => ConceptReviewReason::class,
    ];

    public function sense(): BelongsTo
    {
        return $this->belongsTo(Sense::class);
    }
}
