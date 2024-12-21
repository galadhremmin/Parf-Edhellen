<?php

namespace App\Models;

class Translation extends ModelBase
{
    protected $fillable = [
        'gloss_id', 'translation',
    ];

    protected $hidden = [
        'gloss_id',
        'created_at',
        'updated_at',
    ];

    public function gloss()
    {
        return $this->belongsTo(Gloss::class);
    }
}
