<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    public function author() 
    {
        return $this->belongsTo(Author::class);
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

    public function keywords() 
    {
        return $this->hasMany(Keyword::class);
    }

    public function word() 
    {
        return $this->belongsTo(Word::class);
    }

    public function scopeNotDeleted($query)
    {
        $query->where('is_deleted', 0);
    }

    public function scopeLatest($query)
    {
        $query->where('is_latest', 1);
    }
}
