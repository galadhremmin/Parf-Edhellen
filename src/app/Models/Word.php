<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Word extends ModelBase implements Interfaces\IHasFriendlyName
{
    use Traits\HasAccount;

    protected $fillable = ['account_id', 'word', 'normalized_word', 'reversed_normalized_word'];

    public function scopeForString($query, string $word)
    {
        $query->where('word', $word);
    }

    public function getFriendlyName() 
    {
        return $this->word;
    }
}
