<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Inflection extends ModelBase
{
    protected $fillable = ['name', 'group_name', 'is_restricted'];

    protected $hidden = ['created_at', 'updated_at'];

    public function lexical_entry_inflections(): HasMany
    {
        return $this->hasMany(LexicalEntryInflection::class);
    }
}
