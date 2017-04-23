<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    public function author() 
    {
        return $this->hasOne(Author::class);
    }

    public function group() 
    {
        return $this->hasOne(TranslationGroup::class);
    }
    
    public function language() 
    {
        return $this->hasOne(Language::class);
    }

    public function word() 
    {
        return $this->hasOne(Word::class);
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
