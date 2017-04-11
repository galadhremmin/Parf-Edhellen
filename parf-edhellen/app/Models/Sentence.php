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
        return $this->hasMany(SentenceFragment::class, 'SentenceID', 'SentenceID');
    }

    public function language() 
    {
        return $this->hasOne(Language::class, 'ID', 'LanguageID');
    }
}
