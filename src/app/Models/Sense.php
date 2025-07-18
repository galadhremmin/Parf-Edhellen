<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sense extends ModelBase
{
    protected $fillable = ['id', 'description'];

    public $incrementing = false;

    public function word(): BelongsTo
    {
        return $this->belongsTo(Word::class, 'id', 'id');
    }

    public function glosses(): HasMany
    {
        return $this->hasMany(Gloss::class);
    }

    public function keywords(): HasMany
    {
        return $this->hasMany(Keyword::class);
    }

    public function scopeForString($query, string $word)
    {
        $query->join('words', 'senses.id', 'words.id')
            ->where('words.word', $word);
    }
}
