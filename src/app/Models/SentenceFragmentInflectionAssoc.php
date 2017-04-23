<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SentenceFragmentInflectionAssoc extends Model
{
    protected $table = 'sentence_fragment_inflection';

    /**
     * Disable automatic timestamps.
     */
    public $timestamps = false;

    public function sentenceFragment()
    {
        return $this->belongsTo(SentenceFragment::class, 'FragmentID', 'FragmentID');
    }

    public function inflection()
    {
        return $this->hasOne(Inflection::class, 'InflectionID', 'InflectionID');
    }
}
