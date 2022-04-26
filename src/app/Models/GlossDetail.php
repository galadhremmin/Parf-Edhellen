<?php

namespace App\Models;

class GlossDetail extends ModelBase
{
    use Traits\HasAccount;
    
    protected $fillable = [ 
        'gloss_id', 'category', 'text', 'order', 'type'
    ];

    public function gloss() 
    {
        return $this->belongsTo(Gloss::class);
    }
}
