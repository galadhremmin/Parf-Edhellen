<?php

namespace App\Models;

class TranslationGroup extends ModelBase
{
    public function translations() 
    {
        return $this->belongsTo(Translation::class);
    }
}
