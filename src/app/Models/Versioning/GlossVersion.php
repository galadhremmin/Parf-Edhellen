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

class GlossVersion extends ModelBase implements IHasFriendlyName
{
    use HasAccount;

    protected $fillable = [
        'account_id', 'language_id', 'word_id', 'speech_id', 'gloss_group_id', 'sense_id',
        'source', 'comments', 'is_uncertain', 'is_rejected', 'tengwar', 'word_id', 'external_id',
        'has_details', 'label', 'source', 'etymology', 'version_change_flags', 'gloss_id',
    ];

    public function gloss()
    {
        return $this->belongsTo(Gloss::class);
    }

    public function gloss_details()
    {
        return $this->hasMany(GlossDetailVersion::class);
    }

    public function translations()
    {
        return $this->hasMany(TranslationVersion::class);
    }

    public function sense()
    {
        return $this->belongsTo(Sense::class);
    }

    public function gloss_group()
    {
        return $this->belongsTo(GlossGroup::class);
    }

    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    public function word()
    {
        return $this->belongsTo(Word::class);
    }

    public function speech()
    {
        return $this->belongsTo(Speech::class);
    }

    public function getFriendlyName()
    {
        return $this->word->word;
    }
}
