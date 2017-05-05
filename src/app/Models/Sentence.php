<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sentence extends Model
{
    public function fragments() 
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
        return $this->hasOne(Account::class);
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