<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sentence extends Model
{
    protected $table = 'sentence';
    protected $primaryKey = 'SentenceID';

    /**
     * Disable automatic timestamps.
     */
    public $timestamps = false;

    public function fragments() 
    {
        return $this->hasMany(SentenceFragment::class, 'SentenceID', 'SentenceID')
            ->orderBy('Order');
    }

    public function language() 
    {
        return $this->hasOne(Language::class, 'ID', 'LanguageID');
    }

    public function author()
    {
        return $this->hasOne(Author::class, 'AccountID', 'AuthorID');
    }
    
    public function scopeNeologisms($query)
    {
        $query->where('Neologism', 1);
    }

    public function scopeApproved($query)
    {
        $query->where('Approved', 1);
    }

    public function scopeByLanguage($query, int $langId)
    {
        $query->where('LanguageID', $langId);
    }
}
