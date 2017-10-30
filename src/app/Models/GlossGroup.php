<?php

namespace App\Models;

class GlossGroup extends ModelBase
{
    public function glosses() 
    {
        return $this->hasMany(Gloss::class);
    }
}
