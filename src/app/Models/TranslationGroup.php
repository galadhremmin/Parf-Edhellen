<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TranslationGroup extends Model
{
    protected $table = 'translation_group';
    protected $primaryKey = 'TranslationGroupID';

    /**
     * Disable automatic timestamps.
     */
    public $timestamps = false;

    public function translations() 
    {
        return $this->belongsTo(Translation::class, 'TranslationGroupID', 'TranslationGroupID');
    }
}
