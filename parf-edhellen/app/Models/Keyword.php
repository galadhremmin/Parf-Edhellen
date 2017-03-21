<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Keyword extends Model
{
    protected $table = 'keywords';
    protected $primaryKey = 'RelationID';

    public function scopeFindByTerm($query, string $term, $reversed = false) {
        $query->distinct()
            ->where($reversed ? 'ReversedNormalizedKeyword' : 'NormalizedKeyword', 'like', $term)
            ->orderBy('Keyword', 'asc');
    }
}
