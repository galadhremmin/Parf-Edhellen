<?php

namespace App\Models;

class Translation extends ModelBase
{
    protected $fillable = [ 
        'gloss_id', 'translation'
    ];
    protected $hidden = [
        'created_at',
        'updated_at' 
    ];

    public function gloss() 
    {
        return $this->belongsTo(Gloss::class);
    }
}
