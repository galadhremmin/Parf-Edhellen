<?php

namespace App\Models;

class Inflection extends ModelBase
{
    public function sentence_fragment_associations()
    {
        return $this->hasMany(SentenceFragmentInflectionRel::class);
    }
}
