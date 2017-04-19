<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Inflection extends Model
{
    protected $table = 'inflection';
    protected $primaryKey = 'InflectionID';

    /**
     * Disable automatic timestamps.
     */
    public $timestamps = false;

    public function speech() 
    {
        return $this->belongsTo(Speech::class, 'SpeechID', 'SpeechID');
    }

    public function sentenceFragments() 
    {
        return $this->hasMany(SentenceFragment::class, 'InflectionID', 'InflectionID');
    }
}