<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Speech extends Model
{
    protected $table = 'speech';
    protected $primaryKey = 'SpeechID';

    /**
     * Disable automatic timestamps.
     */
    public $timestamps = false;

    public function inflections() 
    {
        return $this->hasMany(Inflection::class, 'SpeechID', 'SpeechID');
    }

    public function sentenceFragments()
    {
        return $this->hasMany(SentenceFragment::class, 'SpeechID', 'SpeechID');
    }
}