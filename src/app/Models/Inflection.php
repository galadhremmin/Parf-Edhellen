<?php

namespace App\Models;

class Inflection extends ModelBase
{
    protected $hidden = [ 'created_at', 'updated_at' ];

    public function sentence_fragment_associations()
    {
        return $this->hasMany(SentenceFragmentInflectionRel::class);
    }
}
