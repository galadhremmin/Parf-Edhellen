<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WordNetSynset extends ModelBase
{
    protected $table = 'wordnet_synsets';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['id', 'pos', 'label', 'lexname', 'definition'];

    public function senses(): HasMany
    {
        return $this->hasMany(WordNetSense::class, 'synset_id');
    }

    public function hypernyms(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'wordnet_hypernyms', 'synset_id', 'hypernym_id')
            ->withPivot('is_instance');
    }
}
