<?php

namespace App\Models;

class SearchViewHourlyStat extends ModelBase
{
    protected $table = 'search_view_hourly_stats';

    protected $fillable = ['hour', 'views'];

    protected function casts(): array
    {
        return [
            'hour' => 'datetime',
        ];
    }
}
