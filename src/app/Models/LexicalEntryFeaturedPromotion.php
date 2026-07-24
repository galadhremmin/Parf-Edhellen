<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LexicalEntryFeaturedPromotion extends ModelBase
{
    protected $table = 'lexical_entry_featured_promotions';

    protected $fillable = [
        'account_id', 'search_word', 'language_id', 'lexical_entry_id', 'previous_lexical_entry_id',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function lexical_entry(): BelongsTo
    {
        return $this->belongsTo(LexicalEntry::class);
    }

    public function previous_lexical_entry(): BelongsTo
    {
        return $this->belongsTo(LexicalEntry::class, 'previous_lexical_entry_id');
    }
}
