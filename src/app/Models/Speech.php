<?php

namespace App\Models;

class Speech extends ModelBase
{
    protected $hidden = [ 'created_at', 'updated_at' ];

    public function sentence_fragments()
    {
        return $this->hasMany(SentenceFragment::class);
    }
}
