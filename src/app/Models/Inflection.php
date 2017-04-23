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

    public function sentenceFragmentAssociations()
    {
        return $this->hasMany(SentenceFragmentInflectionAssoc::class, 'InflectionID', 'InflectionID');
    }
}