<?php

namespace App\Models\Versioning;

use App\Models\Gloss;
use App\Models\GlossGroup;
use App\Models\Interfaces\IHasFriendlyName;
use App\Models\Language;
use App\Models\ModelBase;
use App\Models\Sense;
use App\Models\Speech;
use App\Models\Traits\HasAccount;
use App\Models\Word;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GlossVersion extends ModelBase implements IHasFriendlyName
{
    use HasAccount;

    protected $fillable = [
        'account_id', 'language_id', 'word_id', 'speech_id', 'gloss_group_id', 'sense_id',
        'source', 'comments', 'is_uncertain', 'is_rejected', 'tengwar', 'word_id', 'external_id',
        'has_details', 'label', 'source', 'etymology', 'version_change_flags', 'gloss_id',
    ];

    public function gloss(): BelongsTo
    {
        return $this->belongsTo(Gloss::class);
    }

    public function gloss_details(): HasMany
    {
        return $this->hasMany(GlossDetailVersion::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(TranslationVersion::class);
    }

    public function sense(): BelongsTo
    {
        return $this->belongsTo(Sense::class);
    }

    public function gloss_group(): BelongsTo
    {
        return $this->belongsTo(GlossGroup::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function word(): BelongsTo
    {
        return $this->belongsTo(Word::class);
    }

    public function speech(): BelongsTo
    {
        return $this->belongsTo(Speech::class);
    }

    public function getFriendlyName()
    {
        return $this->word->word;
    }
}
