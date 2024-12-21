<?php

namespace App\Models;

class Sense extends ModelBase
{
    protected $fillable = ['id', 'description'];

    public $incrementing = false;

    public function word()
    {
        return $this->belongsTo(Word::class, 'id', 'id');
    }

    public function glosses()
    {
        return $this->hasMany(Gloss::class);
    }

    public function keywords()
    {
        return $this->hasMany(Keyword::class);
    }

    public function scopeForString($query, string $word)
    {
        $query->join('words', 'senses.id', 'words.id')
            ->where('words.word', $word);
    }
}
