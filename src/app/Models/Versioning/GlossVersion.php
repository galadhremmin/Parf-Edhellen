<?php

namespace App\Models\Versioning;

use App\Models\Interfaces\IHasFriendlyName;
use App\Models\Traits\HasAccount;
use App\Models\{
    Account,
    Gloss,
    GlossGroup,
    Language,
    ModelBase,
    Sense,
    Speech,
    Translation,
    Word
};

class GlossVersion extends ModelBase implements IHasFriendlyName
{
    use HasAccount;
    
    protected $fillable = [
        'version_created_at', 'gloss_id', 'language_id', 'word_id', 'account_id',
        'sense_id', 'gloss_group_id', 'speech_id', 'is_uncertain', 'is_rejected',
        'has_details', 'etymology', 'tengwar', 'source', 'comments', 'external_id',
        'label'
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
