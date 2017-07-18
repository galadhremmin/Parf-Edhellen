<?php

namespace App\Models;

class Language extends ModelBase
{
    protected $hidden = ['created_at', 'updated_at', 'order', 'category'];

    public function scopeInvented($query) 
    {
        return $query->where('is_invented', 1);
    }

    public function scopeOrderByPriority($query, $direction = 'desc') 
    {
        return $query->orderBy('order', $direction);
    }
}
