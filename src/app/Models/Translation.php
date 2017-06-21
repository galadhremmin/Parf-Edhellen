<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    public function account() 
    {
        return $this->belongsTo(Account::class);
    }

    public function sense() 
    {
        return $this->belongsTo(Sense::class);
    }

    public function translation_group() 
    {
        return $this->belongsTo(TranslationGroup::class);
    }
    
    public function language() 
    {
        return $this->belongsTo(Language::class);
    }

    public function word() 
    {
        return $this->belongsTo(Word::class);
    }

    public function keywords() 
    {
        return $this->hasMany(Keyword::class);
    }

    public function sentence_fragments() 
    {
        return $this->hasMany(SentenceFragment::class);
    }

    public function translation_reviews() 
    {
        return $this->hasMany(TranslationReview::class);
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
        return Translation::where('child_translation_id', $this->id)
            ->get();
    }

    public function getOrigin()
    {
        if (! $this->origin_translation_id) {
            return $this;
        }

        return Translation::where('origin_translation_id', $this->id)
            ->get();
    }

    public function getChild()
    {
        if (! $this->child_translation_id) {
            return null;
        }

        return Translation::find($this->child_translation_id);
    }

    public function getLatestVersion() 
    {
        return $this->is_latest 
            ? $this
            : Translation::where([
                [ 'origin_translation_id', '=', $this->origin_translation_id ?: $this->id ],
                [ 'is_latest', '=', 1]
            ])->first();
    }
}
