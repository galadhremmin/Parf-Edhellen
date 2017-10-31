<?php

namespace App\Models;

class Gloss extends ModelBase
{
    use Traits\HasAccount;
    
    protected $fillable = [ 
        'account_id', 'language_id', 'word_id', 'speech_id', 'gloss_group_id', 'sense', 
        'source', 'comments', 'notes', 'is_uncertain', 'is_rejected', 'tengwar',
        'word', 'external_id'
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

    public function favourites() 
    {
        return $this->hasMany(Favourite::class);
    }

    public function scopeNotDeleted($query)
    {
        $query->where('is_deleted', 0);
    }

    public function scopeNotIndex($query)
    {
        $query->where('is_index', 0);
    }

    public function scopeLatest($query)
    {
        $query->where('is_latest', 1);
    }

    public function scopeActive($query) 
    {
        $this->scopeNotDeleted($query);
        $this->scopeNotIndex($query);
        $this->scopeLatest($query);
    }

    public function getParent()
    {
        return Gloss::where('child_gloss_id', $this->id)
            ->get();
    }

    public function getOrigin()
    {
        if (! $this->origin_gloss_id) {
            return $this;
        }

        return Gloss::where('origin_gloss_id', $this->id)
            ->get();
    }

    public function getChild()
    {
        if (! $this->child_gloss_id) {
            return null;
        }

        return Gloss::find($this->child_gloss_id);
    }

    public function getLatestVersion() 
    {
        return $this->is_latest 
            ? $this
            : Gloss::where([
                [ 'origin_gloss_id', '=', $this->origin_gloss_id ?: $this->id ],
                [ 'is_latest', '=', 1]
            ])->first() ?: $this;
    }
}
