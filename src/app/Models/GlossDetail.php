<?php

namespace App\Models;

class GlossDetail extends ModelBase
{
    use Traits\HasAccount;
    
    protected $fillable = [ 
        'account_id', 'gloss_id', 'category', 'text', 'order'
    ];

    public function gloss() 
    {
        return $this->belongsTo(Gloss::class);
    }
}
