<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Inflection extends Model
{
    public function sentenceFragmentAssociations()
    {
        return $this->hasMany(SentenceFragmentInflectionAssoc::class);
    }
}