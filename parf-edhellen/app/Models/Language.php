<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $table = 'language';
    protected $primaryKey = 'ID';

    public function scopeInvented($query) {
        return $query->where('Invented', '=', 1);
    }

    public function scopeOrderByPriority($query, $direction = 'asc') {
        return $query->orderBy('Order', $direction);
    }
}
