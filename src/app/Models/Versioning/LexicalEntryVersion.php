<?php

namespace App\Models\Versioning;

use App\Models\LexicalEntry;
use App\Models\LexicalEntryGroup;
use App\Models\Interfaces\IHasFriendlyName;
use App\Models\Language;
use App\Models\ModelBase;
use App\Models\Sense;
use App\Models\Speech;
use App\Models\Traits\HasAccount;
use App\Models\Word;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LexicalEntryVersion extends ModelBase implements IHasFriendlyName
{
    use HasAccount;

    protected $table = 'lexical_entry_versions';

    protected $fillable = [
        'account_id', 'language_id', 'word_id', 'speech_id', 'lexical_entry_group_id', 'sense_id',
        'source', 'comments', 'is_uncertain', 'is_rejected', 'tengwar', 'word_id', 'external_id',
        'has_details', 'label', 'source', 'etymology', 'version_change_flags', 'lexical_entry_id',
    ];

    public function lexical_entry(): BelongsTo
    {
        return $this->belongsTo(LexicalEntry::class, 'lexical_entry_id');
    }

    public function lexical_entry_details(): HasMany
    {
        return $this->hasMany(LexicalEntryDetailVersion::class, 'lexical_entry_version_id');
    }

    public function glosses(): HasMany
    {
        return $this->hasMany(GlossVersion::class, 'lexical_entry_version_id');
    }

    public function sense(): BelongsTo
    {
        return $this->belongsTo(Sense::class);
    }

    public function lexical_entry_group(): BelongsTo
    {
        return $this->belongsTo(LexicalEntryGroup::class, 'lexical_entry_group_id');
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
