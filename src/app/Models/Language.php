<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $hidden = ['created_at', 'updated_at', 'order', 'category'];

    public function scopeInvented($query) 
    {
        return $query->where('is_invented', 1);
    }

    public function scopeOrderByPriority($query, $direction = 'asc') 
    {
        return $query->orderBy('order', $direction);
    }
}
