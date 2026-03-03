<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SearchViewEvent extends ModelBase
{
    protected $table = 'search_view_events';

    public $timestamps = false;

    protected $fillable = ['search_id', 'viewed_at'];

    protected function casts(): array
    {
        return [
            'viewed_at' => 'datetime',
        ];
    }

    public function searchDefinition(): BelongsTo
    {
        return $this->belongsTo(SearchDefinition::class, 'search_id', 'id');
    }
}
