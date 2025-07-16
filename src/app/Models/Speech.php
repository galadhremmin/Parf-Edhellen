<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Speech extends ModelBase
{
    protected $fillable = ['name', 'order', 'is_verb'];

    protected $hidden = ['created_at', 'updated_at'];

    public function sentence_fragments(): HasMany
    {
        return $this->hasMany(SentenceFragment::class);
    }
}
