<?php

namespace App\Models;

use App\Repositories\Enumerations\ConceptReviewReason;
use App\Repositories\Enumerations\ConceptSource;
use App\Services\Enumerations\ConceptOutcome;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One decision about what a sense means, kept as it was made. Rows are never changed or removed.
 */
class SenseConceptDecision extends ModelBase
{
    const UPDATED_AT = null;

    protected $fillable = [
        'sense_id', 'sense', 'source', 'outcome', 'decided_by', 'candidate_synset_ids', 'synset_ids', 'better_word',
        'relation', 'confidence', 'concepts', 'review_reason', 'prompt_hash', 'account_id',
    ];

    protected $casts = [
        'source' => ConceptSource::class,
        'outcome' => ConceptOutcome::class,
        'review_reason' => ConceptReviewReason::class,
        'candidate_synset_ids' => 'array',
        'synset_ids' => 'array',
        'concepts' => 'array',
    ];

    public function sense(): BelongsTo
    {
        return $this->belongsTo(Sense::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
