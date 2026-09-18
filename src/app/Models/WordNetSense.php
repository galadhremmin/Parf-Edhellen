<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A lemma's membership of a synset, with how often that sense was tagged in WordNet's corpus.
 */
class WordNetSense extends ModelBase
{
    protected $table = 'wordnet_senses';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['lemma', 'synset_id', 'pos', 'sense_number', 'tag_count'];

    public function synset(): BelongsTo
    {
        return $this->belongsTo(WordNetSynset::class, 'synset_id');
    }
}
