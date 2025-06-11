<?php

namespace App\Models;

use App\Models\Versioning\GlossVersion;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Gloss extends ModelBase implements Interfaces\IHasFriendlyName, Interfaces\IHasLanguage
{
    use Traits\HasAccount;

    protected $fillable = [
        'account_id', 'language_id', 'word_id', 'speech_id', 'gloss_group_id', 'sense_id',
        'source', 'comments', 'is_uncertain', 'is_rejected', 'is_deleted', 'tengwar',
        'word_id', 'external_id', 'has_details', 'label', 'latest_gloss_version_id',
        'source', 'etymology',
    ];

    /**
     * @return HasMany<Translation>
     */
    public function translations(): HasMany
    {
        return $this->hasMany(Translation::class);
    }

    /**
     * @return BelongsTo<Sense>
     */
    public function sense(): BelongsTo
    {
        return $this->belongsTo(Sense::class);
    }

    /**
     * @return BelongsTo<GlossGroup>
     */
    public function gloss_group(): BelongsTo
    {
        return $this->belongsTo(GlossGroup::class);
    }

    /**
     * @return HasMany<GlossVersion>
     */
    public function gloss_versions(): HasMany
    {
        return $this->hasMany(GlossVersion::class);
    }

    /**
     * @return BelongsTo<Language>
     */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    /**
     * @return BelongsTo<Word>
     */
    public function word(): BelongsTo
    {
        return $this->belongsTo(Word::class);
    }

    /**
     * @return BelongsTo<Speech>
     */
    public function speech(): BelongsTo
    {
        return $this->belongsTo(Speech::class);
    }

    /**
     * @return HasMany<GlossDetail>
     */
    public function gloss_details(): HasMany
    {
        return $this->hasMany(GlossDetail::class);
    }

    /**
     * @return HasMany<Keyword>
     */
    public function keywords(): HasMany
    {
        return $this->hasMany(Keyword::class);
    }

    /**
     * @return HasMany<SentenceFragment>
     */
    public function sentence_fragments(): HasMany
    {
        return $this->hasMany(SentenceFragment::class);
    }

    /**
     * @return HasMany<Contribution>
     */
    public function contributions(): HasMany
    {
        return $this->hasMany(Contribution::class);
    }

    /**
     * @return HasMany<FlashcardResult>
     */
    public function flashcard_results(): HasMany
    {
        return $this->hasMany(FlashcardResult::class);
    }

    /**
     * @return HasMany<GlossInflection>
     */
    public function gloss_inflections(): HasMany
    {
        return $this->hasMany(GlossInflection::class);
    }

    /**
     * @return HasMany<AccountFeed>
     */
    public function account_feed(): HasMany
    {
        return $this->hasMany(AccountFeed::class);
    }

    public function scopeNotDeleted($query)
    {
        $query->where('is_deleted', 0);
    }

    public function scopeNotUncertain($query)
    {
        $query->where([
            ['is_uncertain', 0],
            ['is_rejected', 0],
        ]);
    }

    public function scopeActive($query)
    {
        $this->scopeNotDeleted($query);
    }

    public function getFriendlyName()
    {
        return $this->word->word;
    }
}
