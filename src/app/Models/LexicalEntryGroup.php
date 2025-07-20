<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class LexicalEntryGroup extends ModelBase
{
    protected $table = 'lexical_entry_groups';

    protected $fillable = ['name', 'external_link_format', 'is_canon', 'is_old', 'label'];

    public function lexical_entries(): HasMany
    {
        return $this->hasMany(LexicalEntry::class, 'lexical_entry_group_id');
    }

    public function scopeSafe($query)
    {
        $query->where([
            ['is_canon', 1],
            ['is_old', 0],
        ]);
    }
}
