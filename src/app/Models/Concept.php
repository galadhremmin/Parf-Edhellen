<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Concept extends ModelBase
{
    protected $fillable = ['label', 'synset_id', 'parent_id'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function synset(): BelongsTo
    {
        return $this->belongsTo(WordNetSynset::class, 'synset_id');
    }

    /**
     * Every concept below this one, at any depth, and this one itself.
     */
    public function descendants(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'concept_closure', 'ancestor_id', 'descendant_id')->withPivot('depth');
    }

    public function senses(): BelongsToMany
    {
        return $this->belongsToMany(Sense::class, 'sense_concepts')
            ->using(SenseConcept::class)
            ->withPivot(['position', 'source', 'confidence', 'is_locked'])
            ->withTimestamps();
    }
}
