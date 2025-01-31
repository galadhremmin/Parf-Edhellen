<?php

namespace App\Models;

class Inflection extends ModelBase
{
    protected $fillable = ['name', 'group_name', 'is_restricted'];

    protected $hidden = ['created_at', 'updated_at'];

    public function gloss_inflections()
    {
        return $this->hasMany(GlossInflection::class);
    }
}
