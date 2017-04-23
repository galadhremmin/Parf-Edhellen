<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TranslationGroup extends Model
{
    public function translations() 
    {
        return $this->belongsTo(Translation::class);
    }
}
