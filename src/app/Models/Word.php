<?php

namespace App\Models;

class Word extends ModelBase
{
    use Traits\HasAccountTrait;

    protected $fillable = ['account_id', 'word', 'normalized_word', 'reversed_normalized_word'];

    public function scopeForString($query, string $word)
    {
        $query->where('word', $word);
    }
}
