<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Keyword extends Model
{
    protected $table = 'keywords';
    protected $primaryKey = 'RelationID';

    /**
     * Disable automatic timestamps.
     */
    public $timestamps = false;

    public function scopeFindByWord($query, string $word, $reversed = false) 
    {
        $query->distinct()
            ->where($reversed ? 'ReversedNormalizedKeyword' : 'NormalizedKeyword', 'like', $word)
            ->whereNotNull('SenseID')
            ->orderBy('Keyword', 'asc');
    }
}
