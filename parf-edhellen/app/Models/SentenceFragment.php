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
        return $this->belongsTo(Sentence::class, 'SentenceID', 'FragmentID');
    }

    public function isPunctuationOrWhitespace() 
    {
        return preg_match('/^[,\\.!\\?\\s]$/', $this->Fragment);
    }
}
