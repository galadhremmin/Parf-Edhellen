<?php

namespace App\Models;

class SentenceTranslation extends ModelBase
{
    protected $fillable = [
        'sentence_id', 'sentence_number', 'translation'
    ];
}
