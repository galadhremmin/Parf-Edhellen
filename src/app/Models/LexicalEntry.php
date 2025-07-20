<?php

namespace App\Models;

use App\Models\Versioning\LexicalEntryVersion;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LexicalEntry extends ModelBase implements Interfaces\IHasFriendlyName, Interfaces\IHasLanguage
{
    use Traits\HasAccount;

    protected $table = 'lexical_entries';

    protected $fillable = [
        'account_id', 'language_id', 'word_id', 'speech_id', 'lexical_entry_group_id', 'sense_id',
        'source', 'comments', 'is_uncertain', 'is_rejected', 'is_deleted', 'tengwar',
        'word_id', 'external_id', 'has_details', 'label', 'latest_lexical_entry_version_id',
        'source', 'etymology',
    ];

    /**
     * @return HasMany<Gloss>
     */
    public function glosses(): HasMany
    {
        return $this->hasMany(Gloss::class, 'lexical_entry_id');
    }

    /**
     * @return BelongsTo<Sense>
     */
    public function sense(): BelongsTo
    {
        return $this->belongsTo(Sense::class);
    }

    /**
     * @return BelongsTo<LexicalEntryGroup>
     */
    public function lexical_entry_group(): BelongsTo
    {
        return $this->belongsTo(LexicalEntryGroup::class, 'lexical_entry_group_id');
    }

    /**
     * @return HasMany<LexicalEntryVersion>
     */
    public function lexical_entry_versions(): HasMany
    {
        return $this->hasMany(LexicalEntryVersion::class, 'lexical_entry_id');
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
     * @return HasMany<LexicalEntryDetail>
     */
    public function lexical_entry_details(): HasMany
    {
        return $this->hasMany(LexicalEntryDetail::class, 'lexical_entry_id');
    }

    /**
     * @return HasMany<Keyword>
     */
    public function keywords(): HasMany
    {
        return $this->hasMany(Keyword::class, 'lexical_entry_id');
    }

    /**
     * @return HasMany<SentenceFragment>
     */
    public function sentence_fragments(): HasMany
    {
        return $this->hasMany(SentenceFragment::class, 'lexical_entry_id');
    }

    /**
     * @return HasMany<Contribution>
     */
    public function contributions(): HasMany
    {
        return $this->hasMany(Contribution::class, 'lexical_entry_id');
    }

    /**
     * @return HasMany<FlashcardResult>
     */
    public function flashcard_results(): HasMany
    {
        return $this->hasMany(FlashcardResult::class, 'lexical_entry_id');
    }

    /**
     * @return HasMany<LexicalEntryInflection>
     */
    public function lexical_entry_inflections(): HasMany
    {
        return $this->hasMany(LexicalEntryInflection::class, 'lexical_entry_id');
    }

    /**
     * @return HasMany<AccountFeed>
     */
    public function account_feed(): HasMany
    {
        return $this->hasMany(AccountFeed::class, 'lexical_entry_id');
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