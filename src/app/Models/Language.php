<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    public function scopeInvented($query) 
    {
        return $query->where('is_invented', 1);
    }

    public function scopeOrderByPriority($query, $direction = 'asc') 
    {
        return $query->orderBy('order', $direction);
    }
}
