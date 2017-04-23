<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SentenceFragment extends Model
{
    protected $table = 'sentence_fragment';
    protected $primaryKey = 'FragmentID';

    /**
     * Disable automatic timestamps.
     */
    public $timestamps = false;

    public function sentence() 
    {
        return $this->belongsTo(Sentence::class, 'SentenceID', 'SentenceID');
    }

    public function speech()
    {
        return $this->hasOne(Speech::class, 'SpeechID', 'SpeechID');
    }

    public function inflectionAssociations()
    {
        return $this->hasMany(SentenceFragmentInflectionAssoc::class, 'FragmentID', 'FragmentID');
    }

    public function isPunctuationOrWhitespace() 
    {
        return preg_match('/^[,\\.!\\?\\s]$/', $this->Fragment);
    }
}
