<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SentenceFragment extends ModelBase
{
    use SoftDeletes;

    protected $fillable = [
        'fragment', 'tengwar', 'comments', 'speech_id', 'gloss_id', 'sentence_id',
        'order', 'is_linebreak', 'type', 'paragraph_number', 'sentence_number',
    ];

    /**
     * @return BelongsTo<Gloss>
     */
    public function gloss(): BelongsTo
    {
        return $this->belongsTo(Gloss::class);
    }

    /**
     * @return BelongsTo<Sentence>
     */
    public function sentence(): BelongsTo
    {
        return $this->belongsTo(Sentence::class);
    }

    /**
     * @return BelongsTo<Speech>
     */
    public function speech(): BelongsTo
    {
        return $this->belongsTo(Speech::class);
    }

    /**
     * @return HasMany<SentenceFragmentInflectionRel>
     */
    public function inflection_associations__deprecated(): HasMany
    {
        return $this->hasMany(SentenceFragmentInflectionRel::class);
    }

    /**
     * @return HasMany<Keyword>
     */
    public function keywords(): HasMany
    {
        return $this->hasMany(Keyword::class);
    }

    /**
     * @return HasMany<GlossInflection>
     */
    public function gloss_inflections(): HasMany
    {
        return $this->hasMany(GlossInflection::class);
    }
}
