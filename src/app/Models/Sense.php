<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Sense extends ModelBase
{
    protected $fillable = ['id', 'description'];

    public $incrementing = false;

    public function word(): BelongsTo
    {
        return $this->belongsTo(Word::class, 'id', 'id');
    }

    public function lexical_entries(): HasMany
    {
        return $this->hasMany(LexicalEntry::class, 'sense_id');
    }

    public function keywords(): HasMany
    {
        return $this->hasMany(Keyword::class);
    }

    public function terms(): HasMany
    {
        return $this->hasMany(SenseTerm::class)->orderBy('position');
    }

    public function concept_decisions(): HasMany
    {
        return $this->hasMany(SenseConceptDecision::class);
    }

    public function concept_review(): HasOne
    {
        return $this->hasOne(SenseConceptReview::class);
    }

    public function concepts(): BelongsToMany
    {
        return $this->belongsToMany(Concept::class, 'sense_concepts')
            ->using(SenseConcept::class)
            ->withPivot(['position', 'source', 'confidence', 'is_locked'])
            ->withTimestamps();
    }

    public function scopeForString($query, string $word)
    {
        $query->join('words', 'senses.id', 'words.id')
            ->where('words.word', $word);
    }
}
