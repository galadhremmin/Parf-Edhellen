<?php

namespace App\Models;

class GlossGroup extends ModelBase
{
    protected $fillable = ['name', 'external_link_format', 'is_canon', 'is_old', 'label'];

    public function glosses() 
    {
        return $this->hasMany(Gloss::class);
    }
}
