<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WordList extends ModelBase implements Interfaces\IHasFriendlyName
{
    use Traits\HasAccount;

    protected $table = 'word_lists';
    
    protected $fillable = [
        'account_id', 'name', 'description', 'is_public'
    ];

    public function lexical_entries(): BelongsToMany
    {
        return $this->belongsToMany(LexicalEntry::class, 'word_list_entries')
            ->withPivot(['created_at', 'order'])
            ->withTimestamps();
    }

    public function word_list_entries(): HasMany
    {
        return $this->hasMany(WordListEntry::class);
    }

    public function getFriendlyName()
    {
        return $this->name;
    }
}
