<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;


class Sentence extends ModelBase implements Interfaces\IHasFriendlyName, Interfaces\IHasLanguage
{
    use SoftDeletes;
    use Traits\HasAccount;

    protected $fillable = [
        'description', 'language_id', 'source', 'is_neologism', 'is_approved', 'account_id',
        'long_description', 'name',
    ];

    /**
     * @return HasMany<SentenceFragment> 
     */
    public function sentence_fragments(): HasMany
    {
        return $this->hasMany(SentenceFragment::class)
            ->orderBy('order');
    }

    /**
     * @return HasMany<SentenceTranslation>
     */
    public function sentence_translations(): HasMany
    {
        return $this->hasMany(SentenceTranslation::class);
    }

    /**
     * @return BelongsTo<Language>
     */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    /**
     * @return BelongsTo<AccountFeed>
     */
    public function account_feed(): BelongsTo
    {
        return $this->belongsTo(AccountFeed::class);
    }

    public function scopeNeologisms($query)
    {
        $query->where('is_neologism', 1);
    }

    public function scopeApproved($query)
    {
        $query->where('is_approved', 1);
    }

    public function scopeByLanguage($query, int $langId)
    {
        $query->where('language_id', $langId);
    }

    public function getFriendlyName()
    {
        return $this->name;
    }
}
