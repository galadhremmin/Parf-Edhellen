<?php

namespace App\Models;

class Speech extends ModelBase
{
    public function sentence_fragments()
    {
        return $this->hasMany(SentenceFragment::class);
    }
}
