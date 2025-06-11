<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GlossInflection extends ModelBase implements Interfaces\IHasLanguage
{
    protected $fillable = [
        'inflection_group_uuid', 'gloss_id', 'inflection_id', 'account_id', 'sentence_id',
        'sentence_fragment_id', 'order', 'language_id', 'speech_id', 'is_neologism',
        'is_rejected', 'source', 'word', 'sentence_fragment_id',
    ];

    protected $hidden = ['created_at', 'updated_at'];

    public function gloss(): BelongsTo
    {
        return $this->belongsTo(Gloss::class);
    }

    public function sentence(): BelongsTo
    {
        return $this->belongsTo(Sentence::class);
    }

    public function sentence_fragment(): BelongsTo
    {
        return $this->belongsTo(SentenceFragment::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function speech(): BelongsTo
    {
        return $this->belongsTo(Speech::class);
    }

    public function inflection(): BelongsTo
    {
        return $this->belongsTo(Inflection::class);
    }
}
