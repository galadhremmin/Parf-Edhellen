<?php

namespace App\Models;

class Sentence extends ModelBase implements Interfaces\IHasFriendlyName
{
    use Traits\HasAccount;

    protected $fillable = [
        'description', 'language_id', 'source', 'is_neologism', 'is_approved', 'account_id',
        'long_description', 'name'
    ];
    
    public function sentence_fragments() 
    {
        return $this->hasMany(SentenceFragment::class)
            ->orderBy('order');
    }

    public function language() 
    {
        return $this->belongsTo(Language::class);
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

    public function getFriendlyName() 
    {
        return $this->name;
    }
}
