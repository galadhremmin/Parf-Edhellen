<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class GlossGroup extends ModelBase
{
    protected $fillable = ['name', 'external_link_format', 'is_canon', 'is_old', 'label'];

    public function glosses(): HasMany
    {
        return $this->hasMany(Gloss::class);
    }

    public function scopeSafe($query)
    {
        $query->where([
            ['is_canon', 1],
            ['is_old', 0],
        ]);
    }
}
