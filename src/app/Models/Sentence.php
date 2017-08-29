<?php

namespace App\Models;

class Sentence extends ModelBase
{
    use Traits\HasAccountTrait;
    
    public function sentence_fragments() 
    {
        return $this->hasMany(SentenceFragment::class)
            ->orderBy('order');
    }

    public function language() 
    {
        return $this->belongsTo(Language::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
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
}
