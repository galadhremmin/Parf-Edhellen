<?php

namespace App\Models;

use App\Models\Versioning\GlossVersion;

class Gloss extends ModelBase implements Interfaces\IHasFriendlyName
{
    use Traits\HasAccount;
    
    protected $fillable = [ 
        'account_id', 'language_id', 'word_id', 'speech_id', 'gloss_group_id', 'sense_id', 
        'source', 'comments', 'is_uncertain', 'is_rejected', 'is_deleted', 'tengwar',
        'word_id', 'external_id', 'has_details', 'label', 'latest_gloss_version_id',
        'source', 'etymology'
    ];

    public function translations() 
    {
        return $this->hasMany(Translation::class);
    }

    public function sense() 
    {
        return $this->belongsTo(Sense::class);
    }

    public function gloss_group() 
    {
        return $this->belongsTo(GlossGroup::class);
    }

    public function gloss_versions() 
    {
        return $this->hasMany(GlossVersion::class);
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

    public function gloss_details() 
    {
        return $this->hasMany(GlossDetail::class);
    }

    public function keywords() 
    {
        return $this->hasMany(Keyword::class);
    }

    public function sentence_fragments() 
    {
        return $this->hasMany(SentenceFragment::class);
    }

    public function contributions() 
    {
        return $this->hasMany(Contribution::class);
    }

    public function flashcard_results()
    {
        return $this->hasMany(FlashcardResult::class);
    }

    public function scopeNotDeleted($query)
    {
        $query->where('is_deleted', 0);
    }

    public function scopeNotUncertain($query)
    {
        $query->where([
            ['is_uncertain', 0],
            ['is_rejected', 0]
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
