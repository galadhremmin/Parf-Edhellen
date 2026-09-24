<?php

namespace App\Models;

use App\Repositories\Enumerations\ConceptSource;
use Illuminate\Database\Eloquent\Relations\Pivot;

class SenseConcept extends Pivot
{
    protected $table = 'sense_concepts';

    public $incrementing = false;

    protected $fillable = ['sense_id', 'concept_id', 'position', 'source', 'confidence', 'is_locked'];

    protected $casts = [
        'source' => ConceptSource::class,
        'is_locked' => 'boolean',
    ];
}
