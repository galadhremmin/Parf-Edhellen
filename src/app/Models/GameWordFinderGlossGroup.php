<?php

namespace App\Models;

class GameWordFinderGlossGroup extends ModelBase
{
    protected $fillable = [ 
        'gloss_group_id',
    ];

    public function gloss_group()
    {
        return $this->belongsTo(GlossGroup::class);
    }
}
