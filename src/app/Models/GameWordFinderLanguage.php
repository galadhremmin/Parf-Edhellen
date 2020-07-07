<?php

namespace App\Models;

class GameWordFinderLanguage extends ModelBase implements Interfaces\IHasFriendlyName
{
    protected $fillable = [ 
        'language_id', 'title', 'description'
    ];

    protected $primaryKey = 'language_id';

    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    public function getFriendlyName() 
    {
        return $this->title;
    }
}
