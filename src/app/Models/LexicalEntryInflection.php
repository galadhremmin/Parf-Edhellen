<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LexicalEntryInflection extends ModelBase implements Interfaces\IHasLanguage
{
    protected $table = 'lexical_entry_inflections';

    protected $fillable = [
        'inflection_group_uuid', 'lexical_entry_id', 'inflection_id', 'account_id', 'sentence_id',
        'sentence_fragment_id', 'order', 'language_id', 'speech_id', 'is_neologism',
        'is_rejected', 'source', 'word', 'sentence_fragment_id',
    ];

    protected $hidden = ['created_at', 'updated_at'];

    public function lexical_entry(): BelongsTo
    {
        return $this->belongsTo(LexicalEntry::class, 'lexical_entry_id');
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
